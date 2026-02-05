<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification
{

    protected string $changedVia;
    protected string $ipAddress;

    /**
     * Create a new notification instance.
     *
     * @param string $changedVia How the password was changed: 'profile', 'reset', 'admin'
     * @param string|null $ipAddress IP address of the request
     */
    public function __construct(string $changedVia = 'profile', ?string $ipAddress = null)
    {
        $this->changedVia = $changedVia;
        $this->ipAddress = $ipAddress ?? 'Unknown';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $changedViaText = match ($this->changedVia) {
            'profile' => 'melalui halaman profil',
            'reset' => 'menggunakan link reset password',
            'admin' => 'oleh administrator',
            default => 'melalui sistem',
        };

        return (new MailMessage)
            ->subject('🔐 Password Anda Telah Diubah - IDEAL')
            ->greeting('Halo ' . $notifiable->nama . '!')
            ->line('Password akun IDEAL Anda baru saja diubah ' . $changedViaText . '.')
            ->line('**Detail:**')
            ->line('- Waktu: ' . now()->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB')
            ->line('- IP Address: ' . $this->ipAddress)
            ->line('Jika Anda yang melakukan perubahan ini, Anda dapat mengabaikan email ini.')
            ->line('**Jika Anda tidak merasa mengubah password**, segera hubungi administrator sekolah untuk mengamankan akun Anda.')
            ->action('Hubungi Administrator', url('/'))
            ->salutation('Salam, Tim IDEAL');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'changed_via' => $this->changedVia,
            'ip_address' => $this->ipAddress,
            'changed_at' => now()->toIso8601String(),
        ];
    }
}
