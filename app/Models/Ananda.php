<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;
use Spatie\ModelStatus\HasStatuses;
use Storage;


class Ananda extends Model
{
    use SearchableTrait;
    use HasStatuses;
    use HasFactory;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wali()
    {
        return $this->belongsTo(User::class, 'wali_id');
    }

    public function biaya()
    {
        return $this->belongsTo(Biaya::class, 'biaya_id')->withDefault([
            'nama' => 'Tidak ada',
            'jumlah' => 'Rp. 0'
        ]);
    }

    public function tagihan()
    {
        return $this->hasMany(Tagihan::class);
    }

            /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($ananda) {
            $ananda->user_id = auth()->user()->id;
        });
        static::created(function ($ananda) {
            $ananda->setStatus('aktif');
        });
        static::updating(function ($ananda) {
            $ananda->user_id = auth()->user()->id;
        });
    }

    public function getFotoAttribute($value)
    {
        $defaultFoto = 'images/user.png';
        if($value == null){
            return $defaultFoto;
        }
        return (Storage::exists($value)) ? $value : $defaultFoto;
    }

    protected $searchable = [
        'columns' => [
            'nama' => 10,
            'nomor_induk' => 10,
        ],
    ];
}
