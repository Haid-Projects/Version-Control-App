<?php

namespace App\Listeners;

use App\Events\GenerateBackupEvent;
use App\Models\Group;
use App\Notifications\FileReleased;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MakeBackup
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\GenerateBackupEvent  $event
     * @return void
     */
    public function handle(GenerateBackupEvent $event)
    {
        $file = $event->file;
        $group = Group::find($file->group_id);
        foreach($group->users as $user){
            $user->notify(new FileReleased($file));
        }
        //Make a copy from a file
        return "copy made successfully";
    }
}
