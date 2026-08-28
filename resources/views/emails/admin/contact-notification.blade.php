
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Nouveau message de contact</title>
</head>
<body style="margin:0; padding:0; background-color:#F8F9FA; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; line-height:1.6;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#F8F9FA; padding:24px 0;">
  <tr>
    <td align="center">
      <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="max-width:600px; width:100%; background-color:#FFFFFF; border:1px solid #E1E8ED; border-radius:6px; box-shadow:0 2px 6px rgba(44,74,94,0.12);">

        <!-- HEADER -->
        <tr>
          <td style="background-color:#2C4A5E; padding:24px 32px; text-align:center; border-top-left-radius:6px; border-top-right-radius:6px; border-top:3px solid #B8984E;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
              <tr>
                <td align="center">
                  <div style="font-size:28px; font-weight:700; color:#FFFFFF; letter-spacing:0.5px;">
                    SIRAMAMBA <span style="color:#B8984E;">MINING SA</span>
                  </div>
                  <div style="margin-top:8px; font-size:13px; color:#C9A760; letter-spacing:2px; text-transform:uppercase;">
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

            <!-- SUCCESS ICON + HEADLINE -->
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:24px;">
              <tr>
                <td align="center" style="text-align:center;">
                  <div style="width:56px; height:56px; line-height:56px; border-radius:50%; background-color:#EEF3F6; text-align:center; margin:0 auto 16px auto;">
                    <span style="font-size:28px; color:#B8984E;">&#9993;</span>
                  </div>
                  <h1 style="margin:0; font-size:28px; font-weight:700; color:#2C4A5E; line-height:1.3;">
                    Nouveau message re&ccedil;u
                  </h1>
                  <p style="margin:12px 0 0 0; font-size:16px; color:#7A7A7A;">
                    Un visiteur a envoy&eacute; un message via le formulaire de contact du site
                  </p>
                </td>
              </tr>
            </table>

            <!-- DIVIDER -->
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:24px;">
              <tr>
                <td style="border-top:1px solid #E1E8ED;"></td>
              </tr>
            </table>

            <!-- CONTACT INFO CARD -->
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#F5F2ED; border-left:3px solid #B8984E; border-radius:0 6px 6px 0; margin-bottom:24px;">
              <tr>
                <td style="padding:24px;">
                  <h3 style="margin:0 0 16px 0; font-size:18px; font-weight:700; color:#2C4A5E; text-transform:uppercase; letter-spacing:1px;">
                    &mdash; Informations du contact
                  </h3>

                  <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size:16px; color:#2C3E50;">
                    <tr>
                      <td style="padding:10px 0; border-bottom:1px solid #E1E8ED; width:140px; vertical-align:top;">
                        <span style="font-size:12px; font-weight:700; color:#7A7A7A; text-transform:uppercase; letter-spacing:0.5px;">Nom complet</span><br/>
                        <span style="font-weight:700; color:#2C4A5E;">{{ $contact->name }}</span>
                      </td>
                      <td style="padding:10px 0 10px 16px; border-bottom:1px solid #E1E8ED; vertical-align:top;">
                        <span style="font-size:12px; font-weight:700; color:#7A7A7A; text-transform:uppercase; letter-spacing:0.5px;">Email</span><br/>
                        <a href="mailto:{{ $contact->email }}" style="color:#B8984E; text-decoration:none; font-weight:600;">{{ $contact->email }}</a>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:10px 0; border-bottom:1px solid #E1E8ED; vertical-align:top;">
                        <span style="font-size:12px; font-weight:700; color:#7A7A7A; text-transform:uppercase; letter-spacing:0.5px;">T&eacute;l&eacute;phone</span><br/>
                        <a href="tel:{{ $contact->telephone }}" style="color:#2C4A5E; text-decoration:none; font-weight:600;">{{ $contact->telephone }}</a>
                      </td>
                      <td style="padding:10px 0 10px 16px; border-bottom:1px solid #E1E8ED; vertical-align:top;">
                        <span style="font-size:12px; font-weight:700; color:#7A7A7A; text-transform:uppercase; letter-spacing:0.5px;">Date d'envoi</span><br/>
                        <span style="font-weight:600; color:#2C4A5E;">{{ $contact->created_at->isoFormat('D MMMM YYYY [à] HH:mm') }}</span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            <!-- SUBJECT CARD -->
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#EEF3F6; border-radius:6px; margin-bottom:24px;">
              <tr>
                <td style="padding:16px 20px;">
                  <p style="margin:0 0 6px 0; font-size:12px; font-weight:700; color:#7A7A7A; text-transform:uppercase; letter-spacing:1px;">
                    Objet du message
                  </p>
                  <p style="margin:0; font-size:20px; font-weight:700; color:#2C4A5E;">
                    {{ $contact->subject }}
                  </p>
                </td>
              </tr>
            </table>

            <!-- MESSAGE BODY CARD -->
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#FFFFFF; border:1px solid #E1E8ED; border-radius:6px; box-shadow:0 1px 3px rgba(44,74,94,0.08);">
              <tr>
                <td style="padding:24px;">
                  <p style="margin:0 0 12px 0; font-size:12px; font-weight:700; color:#7A7A7A; text-transform:uppercase; letter-spacing:1px;">
                    Contenu du message
                  </p>
                  <p style="margin:0; font-size:16px; color:#2C3E50; line-height:1.8; white-space:pre-wrap;">
                    {{ $contact->message }}
                  </p>
                </td>
              </tr>
            </table>

            <!-- CTA REPLY -->
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:32px;">
              <tr>
                <td align="center" style="text-align:center;">
                  <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                      <td style="border-radius:4px; background-color:#B8984E;">
                        <a href="mailto:{{ $contact->email }}?subject=Re%3A%20{{ rawurlencode($contact->subject) }}&body=Bonjour%20{{ rawurlencode($contact->name) }}%2C%0D%0A%0D%0A"
                           style="display:inline-block; padding:12px 32px; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:16px; font-weight:600; color:#FFFFFF; text-decoration:none;">
                          &#9993; R&eacute;pondre &agrave; {{ $contact->name }}
                        </a>
                      </td>
                    </tr>
                  </table>
                  <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:12px;">
                    <tr>
                      <td style="border-radius:4px; background-color:#FFFFFF; border:2px solid #2C4A5E;">
                        <a href="tel:{{ $contact->telephone }}"
                           style="display:inline-block; padding:10px 28px; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; font-size:16px; font-weight:600; color:#2C4A5E; text-decoration:none;">
                          &phone; Appeler {{ $contact->telephone }}
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
          <td style="background-color:#3D5E74; padding:24px 32px; color:#FFFFFF; font-size:12px; text-align:center; border-bottom-left-radius:6px; border-bottom-right-radius:6px;">
            <div style="font-size:16px; font-weight:700; color:#FFFFFF; margin-bottom:12px;">
              SIRAMAMBA <span style="color:#C9A760;">MINING SA</span>
            </div>
            <p style="margin:0 0 8px 0; color:rgba(255,255,255,0.85); line-height:1.6;">
              &copy; {{ date('Y') }} SIRAMAMBA MINING SA. Tous droits r&eacute;serv&eacute;s.<br/>
              Ceci est un email automatique suite &agrave; la soumission d'un formulaire sur le site.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>
