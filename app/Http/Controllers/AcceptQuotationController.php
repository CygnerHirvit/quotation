<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Notifications\QuotationAccepted;
use App\Notifications\QuotationAcceptedEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * The client accepts a quotation from its public page, with a drawn or a typed signature.
 */
class AcceptQuotationController extends Controller
{
    private const MAX_SIGNATURE_BYTES = 300 * 1024;

    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $quotation = Quotation::withoutGlobalScope('studio')->with('studio')->where('token', $token)->firstOrFail();

        if (! $quotation->canBeAccepted()) {
            return redirect()->to($quotation->publicUrl().'#terms');
        }

        $validated = $request->validateWithBag('accept', [
            'signed_name' => ['required', 'string', 'min:2', 'max:120'],
            'method' => ['required', 'in:draw,type'],
            'signature' => ['required_if:method,draw', 'nullable', 'string', 'max:'.(int) (self::MAX_SIGNATURE_BYTES * 1.4)],
            'agree' => ['accepted'],
        ], [
            'signature.required_if' => 'Please draw your signature, or switch to typing your name.',
            'agree.accepted' => 'Please tick the box to confirm you accept the quotation.',
        ], [
            'signed_name' => 'full name',
        ]);

        $png = $validated['method'] === 'draw' ? $this->decodeSignature($validated['signature']) : null;

        $quotation->accept(
            signedName: trim($validated['signed_name']),
            signaturePng: $png,
            ip: (string) $request->ip(),
            userAgent: $request->userAgent(),
        );

        Notification::send($quotation->teamToNotify(), new QuotationAccepted($quotation));

        if ($quotation->studio->notify_email_on_accept && $quotation->studio->email) {
            Notification::route('mail', $quotation->studio->email)->notify(new QuotationAcceptedEmail($quotation));
        }

        return redirect()->to($quotation->publicUrl().'#terms')->with('accepted', true);
    }

    /**
     * Turn the canvas data URL into PNG bytes, refusing anything that is not a real, reasonably sized PNG.
     */
    private function decodeSignature(?string $dataUrl): string
    {
        $prefix = 'data:image/png;base64,';

        $bytes = is_string($dataUrl) && str_starts_with($dataUrl, $prefix)
            ? base64_decode(substr($dataUrl, strlen($prefix)), true)
            : false;

        $info = $bytes !== false && strlen($bytes) <= self::MAX_SIGNATURE_BYTES ? @getimagesizefromstring($bytes) : false;

        if ($info === false || $info[2] !== IMAGETYPE_PNG) {
            throw ValidationException::withMessages(['signature' => 'The signature could not be read. Please draw it again.'])
                ->errorBag('accept');
        }

        return $bytes;
    }
}
