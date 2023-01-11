<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Pembayaran extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $dates = ['tanggal_bayar', 'tanggal_konfirmasi'];
    protected $with = ['user', 'tagihan'];
    protected $append = ['status_konfirmasi', 'status_style'];

    public function getStatusStyleAttribute()
    {
        if($this->tanggal_konfirmasi == null){
            return 'secondary';
        }
        return 'success';
    }

    /**
     * Get the user's first name.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function statusKonfirmasi(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ($this->tanggal_konfirmasi == null)
            ? 'Belum Dikonfirmasi' : 'Sudah Dikonfirmasi',
        );
    }

    /**
     * Get the tagihan that owns the Pembayaran
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    /**
     * Get the rekeningYayasan that owns the Pembayaran
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rekeningYayasan(): BelongsTo
    {
        return $this->belongsTo(RekeningYayasan::class);
    }

        /**
     * Get the waliBank that owns the Pembayaran
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function waliBank(): BelongsTo
    {
        return $this->belongsTo(WaliBank::class);
    }

    /**
     * Get the user that owns the Pembayaran
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($pembayaran) {
            $pembayaran->user_id = auth()->user()->id;
        });
        static::updating(function ($pembayaran) {
            $pembayaran->user_id = auth()->user()->id;
        });

        static::created(function ($pembayaran) {
            $pembayaran->tagihan->updateStatus();
        });

        static::updated(function ($pembayaran) {
            $pembayaran->tagihan->updateStatus();
        });

        static::deleted(function ($pembayaran) {
            // ubah status tagihan
            $pembayaran->tagihan->updateStatus();

            // if pembayaran == 'tf' hapus bukti bayar
            if($pembayaran->metode_pembayaran == 'transfer'){
                if($pembayaran->bukti_bayar != null && Storage::exists($pembayaran->bukti_bayar)){
                    Storage::delete($pembayaran->bukti_bayar);
                }
            }

            // if ada wali, hapus wali->notifikasi($pembayaran)
            if($pembayaran->wali != null){
                $notifications = $pembayaran->wali->notifications->where('type', 'App\Notifications\PembayaranKonfirmasiNotification');
                foreach ($notifications as $notification) {
                    if($notification['data']['pembayaran_id'] == $pembayaran->id){
                        $notification->delete();
                    }
                }
            }

            // if ada operator, hapus operator->notifikas($pembayaran)
            $userOperator = User::where('akses', 'operator')->get();
            foreach ($userOperator as $user) {
                $notification = $user->notifications->where('type', 'App\Notifications\PembayaranNotification')->filter(function($item) use($pembayaran){
                    return $item['data']['pembayaran_id'] == $pembayaran->id;
                })->first();
                if($notification != null){
                    $notification->delete();
                }
            }
        });
    }

    /**
     * Get the wali that owns the Pembayaran
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wali(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_id');
    }
}
