<?php

namespace App\Http\Middleware;

use App\Models\Group;
use App\Models\Member;
use Closure;
use Illuminate\Http\Request;

class CheckGroupMembership
{
    public function handle(Request $request, Closure $next)
    {
        $group_id = $request->route('group_id');
        $group = Group::find($group_id);
        if(isset($group))
        {
            $member = Member::query()
                ->where('group_id', '=', $group->id)
                ->where('user_id', '=', auth('user')->user()->id)
                ->first();
            if(isset($member))
                return $next($request, $group_id);
            return response()->json('unauthorized to do this action', 403);
        }
        return response()->json('group not found', 404);    }
}
