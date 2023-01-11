<?php

namespace App\Models;

use App\Traits\HasFormatRupiah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Nicolaslopezj\Searchable\SearchableTrait;

class Tagihan extends Model
{
    use SearchableTrait;
    use HasFactory;
    use HasFormatRupiah;
    protected $guarded = [];
    protected $dates = ['tanggal_tagihan', 'tanggal_jatuh_tempo', 'tanggal_lunas'];
    // protected $with = ['user', 'ananda', 'tagihanDetails'];
    protected $with = ['user'];
    // dilaravel 9 append atau nama atribute boleh dihapus saja
    protected $append = ['total_tagihan', 'total_pembayaran', 'status_style'];

    public function getStatusStyleAttribute()
    {
        if($this->status == 'lunas'){
            return 'success';
        }
        if($this->status == 'angsur'){
            return 'warning';
        }
        if($this->status == 'baru'){
            return 'primary';
        }
    }

    /**
     * Get the user's first name.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function totalPembayaran(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->pembayaran()->sum('jumlah_dibayar'),
        );
    }

        /**
     * Get the user's first name.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function totalTagihan(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->tagihanDetails()->sum('jumlah_biaya'),
        );
    }

    /**
     * Get the user that owns the Tagihan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusTagihanWali()
    {
        if($this->status == 'baru'){
            return 'Belum dibayar';
        }else if($this->status == 'lunas'){
            return 'Sudah dibayar';
        }

        return $this->status;
    }

    public function scopeWaliAnanda($q)
    {
        return $q->whereIn('ananda_id', Auth::user()->getAllAnandaId());
    }

    /**
     * Get the user that owns the Tagihan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ananda(): BelongsTo
    {
        return $this->belongsTo(Ananda::class)->withDefault();
    }

    /**
     * Get all of the comments for the Tagihan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tagihanDetails(): HasMany
    {
        return $this->hasMany(TagihanDetail::class);
    }

    /**
     * Get all of the comments for the Tagihan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($tagihan) {
            $tagihan->user_id = auth()->user()->id;
        });
        static::updating(function ($tagihan) {
            $tagihan->user_id = auth()->user()->id;
        });
    }

    public function updateStatus()
    {
        if($this->total_pembayaran >= $this->total_tagihan){
            $tanggalBayar = $this->pembayaran()
            ->orderBy('tanggal_bayar', 'desc')
            ->first()
            ->tanggal_bayar;
            $this->update([
                'status' => 'lunas',
                'tanggal_lunas' => $tanggalBayar,
            ]);
        }

        if($this->total_pembayaran > 0 && $this->total_pembayaran < $this->total_tagihan){
            $this->update(['status' => 'angsur', 'tanggal_lunas' => null]);
        }

        if($this->total_pembayaran <= 0){
            $this->update([
                'status' => 'baru',
                'tanggal_lunas' => null,
            ]);
        }
    }

    protected $searchable = [
        'columns' => [
            'anandas.nama' => 10,
            'anandas.nomor_induk' => 9,
        ],
        'joins' => [
            'anandas' => ['anandas.id', 'tagihans.ananda_id'],
        ],
    ];
}
