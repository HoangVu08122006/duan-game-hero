
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $fillable = ['day_index', 'reward_type', 'amount', 'name'];
    public $timestamps = false; // Nếu bạn không dùng created_at/updated_at cho bảng cấu hình này
}