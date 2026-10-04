/**
 * Google sign-in through Firebase.
 *
 * Loaded on demand — see window.loadGoogleSignIn in app.js — so the Firebase SDK,
 * far larger than the rest of this site's JavaScript, never reaches the many
 * visitors who only browse apartments.
 *
 * There are two ways in, and which one is used is decided by configuration:
 *
 *  1. Google Identity Services (preferred, used when a Google OAuth client id is
 *     configured). Google's own library renders the button, returns a Google ID
 *     token, and that token is exchanged for a Firebase session through
 *     identitytoolkit.googleapis.com.
 *
 *  2. signInWithPopup (the Firebase default, used otherwise). Simpler to set up,
 *     but it opens a popup onto <project>.firebaseapp.com/__/auth/handler.
 *
 * The distinction is not academic. That handler page is served from Firebase
 * Hosting, whose address range some networks and ISPs reset outright — the TCP
 * connection is accepted and then killed mid-handshake, so the popup hangs and
 * dies with ERR_CONNECTION_RESET while the rest of Google works normally. Route 1
 * never touches that domain: accounts.google.com serves the button and
 * identitytoolkit.googleapis.com does the exchange.
 *
 * Either way the server sees exactly the same thing — a Firebase ID token it
 * verifies itself — so none of this changes what is trusted.
 */

let appPromise = null;
let gisPromise = null;

async function getAuth(config) {
    if (!appPromise) {
        appPromise = (async () => {
            const [{ initializeApp }, auth] = await Promise.all([
                import('firebase/app'),
                import('firebase/auth'),
            ]);

            const app = initializeApp({
                apiKey: config.apiKey,
                authDomain: config.authDomain,
                projectId: config.projectId,
            });

            const instance = auth.getAuth(app);

            // Sign-in state lives in the Laravel session, not the browser, so
            // there is nothing to persist. This also means signing out of the
            // site really signs you out, rather than leaving a Firebase session
            // that silently signs you back in.
            await auth.setPersistence(instance, auth.inMemoryPersistence);

            return { auth, instance };
        })();
    }

    return appPromise;
}

/**
 * Load Google Identity Services from accounts.google.com.
 */
function loadGis() {
    if (!gisPromise) {
        gisPromise = new Promise((resolve, reject) => {
            if (window.google?.accounts?.id) {
                resolve(window.google);

                return;
            }

            const script = document.createElement('script');
            script.src = 'https://accounts.google.com/gsi/client';
            script.async = true;
            script.onload = () => resolve(window.google);
            script.onerror = () => reject(new Error('Could not load Google sign-in.'));
            document.head.appendChild(script);
        });
    }

    return gisPromise;
}

/**
 * Hand a Firebase ID token to the server and get back where to go next.
 */
async function establishSession(config, idToken) {
    const response = await fetch(config.callbackUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ id_token: idToken }),
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok || !data.success) {
        throw new Error(data.message || 'We could not complete that sign-in. Please try again.');
    }

    return data.redirect;
}

/**
 * Route 1 — render Google's own button, which avoids the Firebase Hosting domain.
 *
 * @param {HTMLElement} container where Google renders its button
 * @param {Function} onDone called with the URL to navigate to
 * @param {Function} onError called with a message to display
 */
export async function renderGoogleButton(config, container, onDone, onError) {
    const google = await loadGis();
    const { auth, instance } = await getAuth(config);

    google.accounts.id.initialize({
        client_id: config.clientId,
        callback: async ({ credential }) => {
            try {
                // credential is a Google ID token. Exchanging it for a Firebase
                // one is a direct API call, not a page load, so the blocked
                // hosting domain is never involved.
                const result = await auth.signInWithCredential(
                    instance,
                    auth.GoogleAuthProvider.credential(credential),
                );

                const idToken = await result.user.getIdToken();
                await auth.signOut(instance);

                onDone(await establishSession(config, idToken));
            } catch (e) {
                onError(describeError(e));
            }
        },
        // Google reports its own failures here rather than by throwing.
        error_callback: (error) => onError(describeGisError(error)),
        auto_select: false,
        cancel_on_tap_outside: true,
    });

    google.accounts.id.renderButton(container, {
        type: 'standard',
        theme: 'outline',
        size: 'large',
        text: 'continue_with',
        shape: 'rectangular',
        logo_alignment: 'left',
        width: Math.min(Math.floor(container.offsetWidth) || 320, 400),
    });

    // If the origin is not on the OAuth client's allowed list, Google refuses to
    // render and leaves the container empty, having logged to the console and
    // nowhere else. Without this check the page just shows a blank gap and the
    // visitor has no idea anything is wrong.
    await new Promise((resolve) => setTimeout(resolve, 1200));

    if (!container.childElementCount) {
        throw new Error(
            `Google refused to render its button for ${window.location.origin}. `
            + 'Add that exact origin, including the port, to the OAuth client\'s '
            + 'Authorised JavaScript origins in the Google Cloud console.',
        );
    }
}

/**
 * Google Identity Services failures, which do not use Firebase's error codes.
 */
function describeGisError(error) {
    switch (error?.type) {
        case 'popup_closed':
            return null;

        case 'popup_failed_to_open':
            return 'Your browser blocked the Google window. Allow pop-ups for this site and try again.';

        default:
            return 'Google could not complete that sign-in. Please try again.';
    }
}

/**
 * Route 2 — Firebase's own popup.
 *
 * @returns {Promise<string>} the URL to navigate to
 */
export async function signInWithGoogle(config) {
    const { auth, instance } = await getAuth(config);

    const provider = new auth.GoogleAuthProvider();
    // Always let people choose, rather than silently reusing whichever Google
    // account the browser happens to be signed into.
    provider.setCustomParameters({ prompt: 'select_account' });

    const credential = await auth.signInWithPopup(instance, provider);
    const idToken = await credential.user.getIdToken();

    await auth.signOut(instance);

    return establishSession(config, idToken);
}

/**
 * Turn an error into something worth showing a person.
 *
 * Returning null means "say nothing" — the visitor closed the popup, which is a
 * choice, not a failure.
 */
export function describeError(error) {
    switch (error?.code) {
        case 'auth/popup-closed-by-user':
        case 'auth/cancelled-popup-request':
        case 'auth/user-cancelled':
            return null;

        case 'auth/popup-blocked':
            return 'Your browser blocked the Google window. Allow pop-ups for this site and try again.';

        case 'auth/account-exists-with-different-credential':
            return 'That email address is already registered another way. Try signing in with your password.';

        case 'auth/network-request-failed':
            // On this project the usual cause is the network resetting
            // connections to the Firebase Hosting domain.
            return 'Could not reach Google. If you are on a network that filters traffic, try another connection.';

        case 'auth/unauthorized-domain':
            return 'This site is not yet authorised for Google sign-in. Please let the host know.';

        case 'auth/operation-not-allowed':
            return 'Google sign-in is not enabled for this site yet. Please let the host know.';

        case 'auth/invalid-credential':
            return 'Google rejected that sign-in. Please try again.';

        default:
            return error?.message || 'We could not complete that sign-in. Please try again.';
    }
}
