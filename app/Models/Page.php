<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

//Activity Log
use App\Traits\MyActivityLogTrait;

class Page extends Model
{
    use MyActivityLogTrait;
    protected $table='pages';
    protected $guarded = [];     
    
    
                            public function scopeWithAdmin($query)
                            {
                                return $query->leftJoin('admin', 'pages.author_admin_id', '=', 'admin.id');
                            }
                            
                        public function admin()
                        {
                            return $this->belongsTo(Admin::class,'author_admin_id','id');
                        }
                        //RELATIONAL METHOD
                        
                            
}

?>
