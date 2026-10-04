# CoastalCharmz

A direct-booking site for a small portfolio of serviced apartments. Guests search
by date, see the full price including fees and taxes before committing, and
request a stay; the host confirms it. Built on Laravel 12.

## Getting started

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link      # required, or uploaded photos 404
npm install && npm run build
php artisan serve
```

Seeded sign-ins (both with password `password`):

| Email | Role |
| --- | --- |
| `admin@admin.com` | host / admin |
| `user@user.com` | guest |

## Sign-in

Two ways in, by design:

- **Guests sign in with Google**, through Firebase Authentication. No password,
  and the account is created on first sign-in.
- **The host signs in with a password**, via the collapsed "Sign in with a
  password" section on `/login`.

The password form is kept deliberately. It is the way back in if the Firebase
project is ever misconfigured, suspended, or pointed at the wrong environment —
without it, a wrong `FIREBASE_PROJECT_ID` would lock everyone out of the site,
including the host.

### Setting up Google sign-in

1. Create a project at [console.firebase.google.com](https://console.firebase.google.com).
2. **Authentication → Sign-in method → Google →** enable.
3. **Authentication → Settings → Authorised domains →** add the domains the site
   runs on.

   Firebase authorises `localhost` out of the box but **not `127.0.0.1`**, and
   they are different hosts as far as it is concerned. `php artisan serve`
   defaults to `127.0.0.1`, which produces `auth/unauthorized-domain` on an
   otherwise correct setup — so `composer dev` passes `--host=localhost`. If you
   start the server by hand, either browse to `http://localhost:8000` or add
   `127.0.0.1` to the authorised list. The port is never part of the check.
4. **Project settings → Your apps → Web app →** copy the config into `.env`:

```ini
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_API_KEY=AIza...
FIREBASE_AUTH_DOMAIN=your-project-id.firebaseapp.com
```

Until those are set, the Google button is replaced by a short notice and password
sign-in carries on working — the page never breaks.

### If the popup fails with ERR_CONNECTION_RESET

Firebase's `signInWithPopup` loads its handler from
`<project>.firebaseapp.com/__/auth/handler`, which is served by Firebase Hosting.
Some networks and ISPs reset connections to that address range: the TCP handshake
is accepted and then killed, so the popup hangs for ~20 seconds and dies, while
every other Google service works normally. `*.web.app` is on the same range and
fails the same way.

The fix is to sign in through Google Identity Services instead, which only needs
`accounts.google.com` and `identitytoolkit.googleapis.com`:

```ini
FIREBASE_GOOGLE_CLIENT_ID=1234567890-abc123.apps.googleusercontent.com
```

Find it in **Firebase console → Authentication → Sign-in method → Google → Web SDK
configuration → Web client ID**. (The same value also appears in Google Cloud
console → APIs & Services → Credentials, as "Web client (auto created by Google
Service)".) It is a public identifier, not a secret.

Google Identity Services checks the page's origin against the OAuth client's
**Authorised JavaScript origins**, which is a *different* list from Firebase's
Authorised domains and, unlike that one, includes the port. For local development
add both:

```
http://localhost:8000
http://127.0.0.1:8000
```

in **Google Cloud console → APIs & Services → Credentials →** that web client. If
the origin is missing, Google silently refuses to render its button; the sign-in
page detects the empty container and says so rather than showing a blank gap.

Setting it switches the front end to Google's own rendered button. The server side
is unchanged: it still receives and verifies a Firebase ID token exactly as before.

`php artisan firebase:check` reports which of these hosts the current network can
actually reach.

All three values are **public identifiers, not secrets**; they appear in the
source of any Firebase web app. There is no service account key, because Firebase
ID tokens are verified against Google's published public certificates. This
application therefore holds no Firebase credential that could leak.

### How a Google sign-in is verified

The browser runs the Google popup and posts the resulting Firebase ID token to
`POST /auth/google/callback`. That token arrives from the client, so it is
untrusted until `App\Services\Firebase\FirebaseTokenVerifier` has checked all of:
the `RS256` algorithm (pinned, so an `alg: none` token cannot slip through), the
`kid` against Google's current certificates, the signature, `aud` equal to the
project id, `iss`, `exp`/`iat`, and a non-empty `sub`.

Then `FirebaseUserResolver` turns that identity into a local user:

- Matching is on **`firebase_uid`**, not email, because a Google account's email
  address can change.
- An existing account is linked by email **only when the provider says the
  address is verified**. Otherwise anyone could register `admin@admin.com` with an
  unverified address and be handed that account.
- `role` is never read from the token. A new Google sign-in is always a guest; an
  existing admin keeps the role their row already has.

`firebase_uid` is intentionally absent from `User::$fillable`, so no request
payload can rebind an account to a different Google identity.

## How availability works

This is the part worth understanding before changing anything.

A stay occupies the **half-open interval `[check_in, check_out)`** — the guest
holds every night from check-in up to but not including check-out. Two stays
overlap only when each starts before the other ends. That is what makes same-day
turnover work: a guest leaving on the 10th and another arriving on the 10th do
not conflict.

`App\Services\AvailabilityService` is the single authority on this. Nothing else
should write its own date-overlap query — search, the property calendar, booking
creation and host confirmation all go through it, so they cannot disagree.

Two things take a night off sale:

1. **A booking** with status `pending` or `confirmed`. Cancelling releases the
   nights immediately, because availability reads the booking's own status.
2. **A `blocked_dates` row**, which is a manual closure by the host and nothing
   else. It is deliberately *not* a mirror of confirmed bookings.

## How pricing works

`App\Services\PricingService` produces a `Quote`, and that same object both
renders the summary the guest reads and populates the booking row. The browser
never computes a figure the guest is shown — it asks
`GET /apartments/{apartment}/quote` and renders the answer.

Rates live in `config/booking.php`:

| Setting | Default | Env |
| --- | --- | --- |
| Service fee | 5% | `BOOKING_SERVICE_FEE_RATE` |
| Tax | 10% | `BOOKING_TAX_RATE` |
| Currency | USD | `BOOKING_CURRENCY` |
| Max nights per stay | 30 | `BOOKING_MAX_NIGHTS` |
| Booking window | 12 months | `BOOKING_WINDOW_MONTHS` |

A cleaning fee is set per property and charged once per stay.

Money is formatted by `App\Support\Money`, not `Number::currency()` — the latter
needs `ext-intl`, which this project's Dockerfile does not install.

## Booking lifecycle

```
guest requests  ──▶  pending  ──▶  confirmed  ──▶  (checkout)  ──▶  reviewable
                        │              │
                        └──────────────┴──▶  cancelled  (nights released)
```

Confirming a booking cancels any other *pending* request overlapping it and
notifies those guests, rather than leaving requests alive against dates they can
never be granted. Only an already-`confirmed` stay can block a confirmation.

Reviews are tied to a booking: one per stay, only after check-out, only by the
guest who stayed. The overall score is the mean of six category scores, so it
cannot be set independently of them.

## Email

Transactional email goes out through **Brevo** over SMTP. Guests are emailed when
a booking is confirmed or cancelled; the host is emailed when a booking comes in.

```ini
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=           # Brevo → SMTP & API → SMTP → Login
MAIL_PASSWORD=           # the SMTP key, NOT your account password
MAIL_FROM_ADDRESS="bookings@yourdomain.com"
MAIL_FROM_NAME="CoastalCharmz"
```

Two things Brevo will reject you for:

- **The password is an SMTP key**, generated under SMTP & API. Your login password
  will fail with `535 Authentication failed`.
- **The from address must be a verified sender** under *Senders, Domains &
  Dedicated IPs*. Free accounts cannot send from an unverified address.

Check it end to end without touching the booking flow:

```bash
php artisan mail:check                      # configuration + reach the server
php artisan mail:check --to=you@example.com # send a real test
php artisan mail:check --booking=1          # send the real confirmation email
```

### The queue is not optional

All three notifications implement `ShouldQueue`, so mail is handed to the queue
rather than sent inside the web request. That stops a slow mail server turning
into a failed booking confirmation — but it means **nothing is sent unless a
worker is running**:

```bash
php artisan queue:work          # production, under supervisor or systemd
```

`composer dev` already runs `queue:listen` locally. If email mysteriously stops
after a deploy, this is almost always why; `php artisan mail:check` reports the
queue connection and how many jobs are waiting.

## Storage

Photos are written to the `public` disk and served via `Storage::url()`. If
`FILESYSTEM_DISK` is `local`, uploads are forced to `public` instead — the
`local` disk roots at `storage/app/private`, which `Storage::url()` cannot serve.
Set `FILESYSTEM_DISK=s3` with the `AWS_*` variables to use object storage.

## Tests

```bash
php artisan test
```

Tests run on in-memory SQLite. Dates are stored through `App\Casts\DateOnly` so a
`DATE` column holds a bare `Y-m-d` string; without it, Laravel's date cast writes
a time component that MySQL truncates and SQLite keeps, which makes availability
comparisons behave differently in tests and production.

## Layout of the code

```
app/
  Services/          AvailabilityService, PricingService, BookingService
  Support/           Quote (price breakdown), Money (intl-free formatting)
  Casts/             DateOnly
  Http/Requests/     StoreBookingRequest, SearchApartmentsRequest
resources/
  css/app.css        design tokens + component classes — the only place colour is defined
  views/components/  property-card, score-badge, search-bar, flash
  views/layouts/     app, navigation, footer
```

No view defines its own palette or font. Colour, spacing, type and elevation all
come from the tokens at the top of `resources/css/app.css`.
