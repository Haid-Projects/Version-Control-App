<?php

namespace App\Http\Middleware;

use App\Models\Group;
use Closure;
use Illuminate\Http\Request;

class CheckGroupOwnership
{
    public function handle(Request $request, Closure $next)
    {
        $group_id = $request->route('group_id');
        $group = Group::find($group_id);
        if(isset($group))
        {
            if($group->user_id === auth('user')->user()->id)
                return $next($request, $group_id);
            return response()->json('unauthorized to do this action', 403);
        }
        return response()->json('group not found', 404);
    }
}
