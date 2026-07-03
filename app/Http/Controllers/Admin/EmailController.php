<?php

namespace App\Http\Controllers\Admin;

use App\Models\BasicExtended;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;


class EmailController extends Controller
{
    public function mailFromAdmin() {
        $data['abe'] = BasicExtended::first();
        return view('admin.basic.email.mail_from_admin', $data);
    }

    public function updateMailFromAdmin(Request $request) {
        $messages = [
            'from_mail.required_if' => 'The from email field is required.',
            'from_name.required_if' => 'The from name field is required.',
            'smtp_host.required_if' => 'The smtp host field is required when smtp is active.',
            'smtp_port.required_if' => 'The smtp port field is required when smtp is active.',
            'encryption.required_if' => 'The encryption field is required when smtp is active.',
            'smtp_username.required_if' => 'The smtp username field is required when smtp is active.',
            'smtp_password.required_if' => 'The smtp password / Brevo API key field is required.',
        ];

        $request->validate([
            'from_mail'      => 'required_if:is_smtp,1|required_if:is_smtp,2',
            'from_name'      => 'required_if:is_smtp,1|required_if:is_smtp,2',
            'is_smtp'        => 'required',
            'smtp_host'      => 'required_if:is_smtp,1',
            'smtp_port'      => 'required_if:is_smtp,1',
            'encryption'     => 'required_if:is_smtp,1',
            'smtp_username'  => 'required_if:is_smtp,1',
            'smtp_password'  => 'required_if:is_smtp,1|required_if:is_smtp,2',
        ], $messages);

        $bes = BasicExtended::all();
        foreach ($bes as $be) {
            $be->from_mail = $request->from_mail;
            $be->from_name = $request->from_name;
            $be->is_smtp   = $request->is_smtp;

            if ($request->is_smtp == 1) {
                $be->smtp_host     = $request->smtp_host;
                $be->smtp_port     = $request->smtp_port;
                $be->encryption    = $request->encryption;
                $be->smtp_username = $request->smtp_username;
                if ($request->filled('smtp_password')) {
                    $be->smtp_password = $request->smtp_password;
                }
            } elseif ($request->is_smtp == 2) {
                if ($request->filled('smtp_password')) {
                    $be->smtp_password = $request->smtp_password;
                }
            }

            $be->save();
        }

        Session::flash('success', 'Mail configuration updated successfully!');
        return back();
    }

    public function testMail(Request $request) {
        $be = BasicExtended::first();

        if ($be->is_smtp == 2) {
            $payload = json_encode([
                'sender'      => ['email' => $be->from_mail, 'name' => $be->from_name],
                'to'          => [['email' => $be->from_mail, 'name' => $be->from_name]],
                'subject'     => 'Test Email from Yumpreneur',
                'htmlContent' => '<p>This is a test email sent via Brevo API. Your configuration is working.</p>',
            ]);
            $ch = curl_init('https://api.brevo.com/v3/smtp/email');
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'api-key: ' . $be->smtp_password],
                CURLOPT_POSTFIELDS     => $payload,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode >= 400) {
                $err = json_decode($response, true);
                return response()->json(['success' => false, 'message' => $err['message'] ?? 'Brevo API error']);
            }
            return response()->json(['success' => true, 'message' => 'Test email sent via Brevo API!']);
        }

        if ($be->is_smtp == 1) {
            try {
                $smtp = [
                    'transport' => 'smtp',
                    'host'      => $be->smtp_host,
                    'port'      => $be->smtp_port,
                    'encryption'=> $be->encryption,
                    'username'  => $be->smtp_username,
                    'password'  => $be->smtp_password,
                    'timeout'   => null,
                    'auth_mode' => null,
                ];
                \Illuminate\Support\Facades\Config::set('mail.mailers.smtp', $smtp);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        }

        try {
            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($be) {
                $message->to($be->from_mail)
                    ->subject('Test Email from Yumpreneur')
                    ->from($be->from_mail, $be->from_name)
                    ->html('<p>This is a test email. Your mail configuration is working.</p>', 'text/html');
            });
            return response()->json(['success' => true, 'message' => 'Test email sent successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function mailToAdmin() {
        $data['abe'] = BasicExtended::first();
        return view('admin.basic.email.mail_to_admin', $data);
    }

    public function updateMailToAdmin(Request $request) {
        $messages = [
            'to_mail.required' => 'Mail Address is required.'
        ];

        $request->validate([
            'to_mail' => 'required',
        ], $messages);

        $bes = BasicExtended::all();
        foreach ($bes as $be) {
            $be->to_mail = $request->to_mail;
            $be->save();
        }

        Session::flash('success', 'Mail address updated successfully!');
        return back();
    }

}
