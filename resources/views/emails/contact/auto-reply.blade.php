<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Accus&eacute; de r&eacute;ception - SIRAMAMBA MINING SA</title>
</head>

<body
    style="margin:0; padding:0; background-color:#F8F9FA; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; line-height:1.6;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
        style="background-color:#F8F9FA; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600"
                    style="max-width:600px; width:100%; background-color:#FFFFFF; border:1px solid #E1E8ED; border-radius:6px; box-shadow:0 2px 6px rgba(44,74,94,0.12);">

                    <!-- HEADER -->
                    <tr>
                        <td
                            style="background-color:#2C4A5E; padding:24px 32px; text-align:center; border-top-left-radius:6px; border-top-right-radius:6px; border-top:3px solid #B8984E;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <div
                                            style="font-size:28px; font-weight:700; color:#FFFFFF; letter-spacing:0.5px;">
                                            SIRAMAMBA <span style="color:#B8984E;">MINING SA</span>
                                        </div>
                                        <div
                                            style="margin-top:8px; font-size:13px; color:#C9A760; letter-spacing:2px; text-transform:uppercase;">
                                            Groupe Minier &amp; Services
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ACCENT DIVIDER -->
                    <tr>
                        <td style="height:3px; background-color:#B8984E;"></td>
                    </tr>

                    <!-- BODY -->
                    <tr>
                        <td style="padding:32px;">

                            <!-- SUCCESS ICON + THANK YOU -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                                style="margin-bottom:24px;">
                                <tr>
                                    <td align="center" style="text-align:center;">
                                        <div
                                            style="width:64px; height:64px; line-height:64px; border-radius:50%; background-color:#F5F2ED; border:2px solid #B8984E; text-align:center; margin:0 auto 20px auto;">
                                            <span
                                                style="font-size:32px; color:#B8984E; font-weight:700;">&#10004;</span>
                                        </div>
                                        <h1
                                            style="margin:0; font-size:28px; font-weight:700; color:#2C4A5E; line-height:1.3;">
                                            Merci, {{ $contact->name }} !
                                        </h1>
                                        <p style="margin:12px 0 0 0; font-size:16px; color:#7A7A7A;">
                                            Votre message a bien &eacute;t&eacute; envoy&eacute; &agrave; notre
                                            &eacute;quipe
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- DIVIDER -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                                style="margin-bottom:24px;">
                                <tr>
                                    <td style="border-top:1px solid #E1E8ED;"></td>
                                </tr>
                            </table>

                            <!-- GREETING + PERSONAL MESSAGE -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                                style="margin-bottom:24px;">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 16px 0; font-size:18px; color:#2C3E50; line-height:1.7;">
                                            Bonjour <strong style="color:#2C4A5E;">{{ $contact->name }}</strong>,
                                        </p>
                                        <p style="margin:0 0 16px 0; font-size:16px; color:#2C3E50; line-height:1.7;">
                                            Nous vous remercions d'avoir pris contact avec <strong>SIRAMAMBA MINING
                                                SA</strong>. Votre message a bien &eacute;t&eacute; re&ccedil;u et est
                                            en cours de traitement par notre &eacute;quipe.
                                        </p>
                                        <p style="margin:0; font-size:16px; color:#2C3E50; line-height:1.7;">
                                            Un membre de notre &eacute;quipe vous recontactera dans les plus brefs
                                            d&eacute;lais.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- HIGHLIGHTED INFO BOX -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                                style="background-color:#F5F2ED; border-left:3px solid #B8984E; border-radius:0 6px 6px 0; margin-bottom:24px;">
                                <tr>
                                    <td style="padding:20px 24px;">
                                        <p
                                            style="margin:0 0 8px 0; font-size:14px; font-weight:700; color:#B8984E; text-transform:uppercase; letter-spacing:1px;">
                                            &#128197; R&eacute;f&eacute;rence de votre demande
                                        </p>
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%">
                                            <tr>
                                                <td style="padding:6px 0; width:140px; font-size:14px; color:#7A7A7A;">
                                                    Objet :
                                                </td>
                                                <td
                                                    style="padding:6px 0; font-size:14px; font-weight:700; color:#2C4A5E;">
                                                    {{ $contact->subject }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0; font-size:14px; color:#7A7A7A;">
                                                    Date :
                                                </td>
                                                <td
                                                    style="padding:6px 0; font-size:14px; font-weight:600; color:#2C3E50;">
                                                    {{ $contact->created_at->isoFormat('D MMMM YYYY [à] HH:mm') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0; font-size:14px; color:#7A7A7A;">
                                                    Dossier n&deg; :
                                                </td>
                                                <td
                                                    style="padding:6px 0; font-size:14px; font-weight:700; color:#B8984E;">
                                                    SMC-{{ str_pad($contact->id, 6, '0', STR_PAD_LEFT) }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- MESSAGE RECAP -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                                style="background-color:#EEF3F6; border-radius:6px; margin-bottom:24px;">
                                <tr>
                                    <td style="padding:24px;">
                                        <p
                                            style="margin:0 0 12px 0; font-size:12px; font-weight:700; color:#7A7A7A; text-transform:uppercase; letter-spacing:1px;">
                                            R&eacute;sum&eacute; de votre message
                                        </p>
                                        <p
                                            style="margin:0; font-size:15px; color:#2C3E50; line-height:1.7; white-space:pre-wrap;">
                                            {{ Str::limit($contact->message, 400) }}
                                        </p>
                                        @if (Str::length($contact->message) > 400)
                                            <p
                                                style="margin:8px 0 0 0; font-size:13px; color:#95A5A6; font-style:italic;">
                                                ... (message tronqu&eacute;, l'original complet est conserv&eacute; dans
                                                nos archives)
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <!-- OUR SERVICES BLOCK -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                                style="border:1px solid #E1E8ED; border-radius:6px; margin-bottom:24px;">
                                <tr>
                                    <td
                                        style="background-color:#FFFFFF; padding:20px 24px; border-bottom:1px solid #E1E8ED;">
                                        <h3 style="margin:0; font-size:18px; font-weight:700; color:#2C4A5E;">
                                            Nos domaines d'expertise
                                        </h3>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:16px 24px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%">
                                            <tr>
                                                <td
                                                    style="width:50%; padding-right:12px; padding-bottom:12px; vertical-align:top;">
                                                    <p style="margin:0; font-size:15px; color:#2C3E50;">
                                                        <span style="color:#B8984E; font-weight:700;">&#9654;</span>
                                                        &nbsp;Exploration mini&egrave;re
                                                    </p>
                                                </td>
                                                <td
                                                    style="width:50%; padding-left:12px; padding-bottom:12px; vertical-align:top;">
                                                    <p style="margin:0; font-size:15px; color:#2C3E50;">
                                                        <span style="color:#B8984E; font-weight:700;">&#9654;</span>
                                                        &nbsp;Extraction Semi-Industrielle
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="width:50%; padding-right:12px; padding-bottom:12px; vertical-align:top;">
                                                    <p style="margin:0; font-size:15px; color:#2C3E50;">
                                                        <span style="color:#B8984E; font-weight:700;">&#9654;</span>
                                                        &nbsp;Commerce d'Or Brut
                                                    </p>
                                                </td>
                                                <td
                                                    style="width:50%; padding-left:12px; padding-bottom:12px; vertical-align:top;">
                                                    <p style="margin:0; font-size:15px; color:#2C3E50;">
                                                        <span style="color:#B8984E; font-weight:700;">&#9654;</span>
                                                        &nbsp;Conseil &amp; Expertise
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width:50%; padding-right:12px; vertical-align:top;">
                                                    <p style="margin:0; font-size:15px; color:#2C3E50;">
                                                        <span style="color:#B8984E; font-weight:700;">&#9654;</span>
                                                        &nbsp;Transport &amp; Logistique
                                                    </p>
                                                </td>
                                                <td style="width:50%; padding-left:12px; vertical-align:top;">
                                                    <p style="margin:0; font-size:15px; color:#2C3E50;">
                                                        <span style="color:#B8984E; font-weight:700;">&#9654;</span>
                                                        &nbsp;Environnement
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- NEED ASSISTANCE CARD -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                width="100%" style="background-color:#2C4A5E; border-radius:6px;">
                                <tr>
                                    <td style="padding:24px; text-align:center; color:#FFFFFF;">
                                        <p style="margin:0 0 12px 0; font-size:18px; font-weight:700; color:#FFFFFF;">
                                            Une question urgente ?
                                        </p>
                                        <p
                                            style="margin:0 0 16px 0; font-size:15px; color:rgba(255,255,255,0.85); line-height:1.6;">
                                            Notre &eacute;quipe est disponible du Lundi au Vendredi, de 8h &agrave; 17h.
                                        </p>
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                                            style="margin:0 auto;">
                                            <tr>
                                                <td style="border-radius:4px; background-color:#B8984E;">
                                                    <a href="tel:+224624000000"
                                                        style="display:inline-block; padding:12px 32px; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:16px; font-weight:600; color:#FFFFFF; text-decoration:none;">
                                                        Tel: {{ env(ADMIN_TEL) }}
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td
                            style="background-color:#3D5E74; padding:24px 32px; color:#FFFFFF; font-size:12px; text-align:center; border-bottom-left-radius:6px; border-bottom-right-radius:6px;">
                            <div style="font-size:16px; font-weight:700; color:#FFFFFF; margin-bottom:12px;">
                                SIRAMAMBA <span style="color:#C9A760;">MINING SA</span>
                            </div>
                            <p style="margin:0 0 10px 0; color:rgba(255,255,255,0.85); line-height:1.6;">
                                Si&egrave;ge Social : {{ env('ADMIN_ADDRESS') }}<br />
                                Conakry, R&eacute;publique de Guin&eacute;e
                            </p>
                            <p style="margin:0; color:rgba(255,255,255,0.70); line-height:1.6;">
                                &copy; {{ date('Y') }} SIRAMAMBA MINING SA. Tous droits
                                r&eacute;serv&eacute;s.<br />
                                Vous recevez cet email car vous avez soumis un formulaire sur notre site.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
