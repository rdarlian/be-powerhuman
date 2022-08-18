<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;

class TeamController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit',10);

        $teamQuery = Team::query();

        //get single data
        if($id)
        {
           $team = $teamQuery->find($id);

            if($team)
            {
                return ResponseFormatter::success($team);
            }
            return ResponseFormatter::error('Team not found');
        }

        //get multiple data
        $teams = $teamQuery->where('company_id', $request->company_id);
       
        if($name){
            $teams->where('name','like','%'.$name . '%');
        }
        return ResponseFormatter::success($teams->paginate($limit),'Teams Found');
    }
    
    public function create(CreateTeamRequest $request)
    {
        try {
            if($request->hasFile('icon')){
                $path = $request->file('icon')->store('public/icons');
            }
    
            $team = Team::create([
                'name' => $request->name,
                'icon' => $path,
                'company_id' => $request->company_id,
            ]);
            
            if(!$team)
            {
                throw new Exception('Team not created');
            }
    
            return ResponseFormatter::success($team, 'Team created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
        
    }
    public function update(UpdateTeamRequest $request, $id)
    {
        try {
            $team = Team::find($id);

            if(!$team)
            {
                throw new Exception('Team not created');
            }
            if($request->hasFile('icon')){
                $path = $request->file('icon')->store('public/icons');
            }

            //update team
            $team->update([
                'name' => $request->name,
                'icon' => isset($path) ? $path : $team->icon,
                'company_id' => $request->company_id,
            ]);

            return ResponseFormatter::success($team, 'Team updated');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            //Get Team
            $team = Team::find($id);

            //check if team exists
            if (!$team) {
                throw new Exception('Team not Found');
            }

            //Delete Team
            $team->delete();

            return ResponseFormatter::success('Team deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(),500);
        }
    }


}
