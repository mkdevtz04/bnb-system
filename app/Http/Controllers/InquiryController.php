<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // Only the validated fields, never the whole request: the model is now
        // fillable, so $request->all() would let anyone set is_read.
        Inquiry::create($validated);

        return back()
            ->with('success', 'Thanks — your message is with the host and they will reply by email.')
            ->withFragment('contact');
    }
}
