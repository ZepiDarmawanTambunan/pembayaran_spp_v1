<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tagihan;

class TagihanDestroy extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        for($i = 0; $i < count($request->tagihan_id); $i++){
            $tagihan = Tagihan::where('id', $request->tagihan_id[$i])->first();

            if($tagihan != null){
                // delete notif dari wali terkait tagihan (klo operator gak ada)
                if($tagihan->ananda->wali != null){
                    $notifications = $tagihan->ananda->wali->notifications->where('type', 'App\Notifications\TagihanNotification');
                    foreach ($notifications as $notification) {
                        if($notification['data']['tagihan_id'] == $tagihan->id){
                            $notification->delete();
                        }
                    }
                }

                // delete pembayarans
                if($tagihan->pembayaran->count() >= 1){
                    $tagihan->pembayaran()->delete();
                }

                // delete tagihan details
                $tagihan->tagihanDetails()->delete();

                // delete tagihan
                $tagihan->delete();
            }
        }
        return response()->json([
            'message' => 'Data berhasil dihapus',
        ], 200);
    }
}
