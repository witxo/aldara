<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $destino = config('mail.contact_address', 'info@ivema.es');

        try {
            Mail::raw(
                "Nombre: {$validated['name']}\nEmail: {$validated['email']}\n\n{$validated['message']}",
                function ($message) use ($validated, $destino) {
                    $message->to($destino)
                        ->subject('[Aldara Contacto] ' . $validated['subject'])
                        ->replyTo($validated['email']);
                }
            );
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al enviar el mensaje. Inténtalo de nuevo más tarde.');
        }

        return redirect()->route('contacto.show')->with('success', 'Mensaje enviado correctamente. Te responderemos pronto.');
    }
}
