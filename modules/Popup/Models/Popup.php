<?php

namespace Modules\Popup\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Modules\Core\Models\SEO;

class Popup extends BaseModel
{
    protected $table = 'bravo_popups';

    protected $fillable = [
        'title',
        'content',
        'status',
    ];

    protected $casts = [
        'include_url'=>'array',
        'exclude_url'=>'array',
    ];

    protected $attributes = [
        'include_url'=>'[{"url":"*"}]',
        'exclude_url'=>'[]',
        'schedule_amount'=>1
    ];

    public static function getModelName()
    {
        return __("Popup");
    }

    public function save(array $options = [])
    {
        Cache::forget('bc_popups');
        return parent::save($options); // TODO: Change the autogenerated stub
    }

    public function delete()
    {
        Cache::forget('bc_popups');
        return parent::delete(); // TODO: Change the autogenerated stub
    }

    public function getDetailUrl($lang = ''){
        return route('home',['preview_popup_id'=>$this->id,'lang'=>$lang]);
    }

    public static function getAll(){
        $value = Cache::rememberForever('bc_popups', function (){
            return parent::query()->where('status','publish')->orderByDesc('id')->get();
        });
        return $value;
    }

    public static function getActive(Request $request){
        $default = $request->query('preview_popup_id');
        $current_url = ltrim($request->getRequestUri(),'/');

        if($default){
            return parent::find($default);
        }
        $popups = static::getAll();
        if(!$popups) return false;

        foreach ($popups as $popup){
            // check expired
            if(isset($_COOKIE['bc_popup_'.$popup->id])){
                continue;
            }
            $flag = true;
            if(!empty($popup->exclude_url)){
                foreach ($popup->exclude_url as $exclude_url){
                    if(empty($exclude_url['url'])) continue;
                    if(is_string_match($current_url,$exclude_url['url'])){
                        $flag = false;
                        break;
                    }
                }
            }
            if(!$flag){// case exclude
                continue;
            }
            if(!empty($popup->include_url)){
                foreach ($popup->include_url as $include_url){
                    if(empty($include_url['url']) or is_string_match($current_url,$include_url['url'])){
                        return $popup;
                    }
                }
            }
        }

        return false;
    }

    public function getExpiredDaysAttribute(){
        if(!$this->schedule_amount) $this->schedule_amount = 1;
        switch ($this->schedule_type){
            case "month":
                return $this->schedule_amount * 30;
                break;
            case "year":
                return $this->schedule_amount * 30 * 12;
                break;
            case "day":
            default:
                return $this->schedule_amount;
                break;
        }
    }

    public function saveCloneByID($clone_id){
        $old = parent::find($clone_id);
        if(empty($old)) return false;
        $old->title = $old->title." - Copy";
        $old->status = 'draft';

        $new = $old->replicate();
        $new->save();
    }
}
