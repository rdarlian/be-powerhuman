<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit',10);

        $companyQuery = Company::with(['users'])->whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        });

        //get single data
        if($id)
        {
           $company = $companyQuery->find($id);

            if($company)
            {
                return ResponseFormatter::success($company);
            }
            return ResponseFormatter::error('Company not found');
        }

        //get multiple data
        $companies = $companyQuery;
       
        if($name){
            $companies->where('name','like','%'.$name . '%');
        }
        return ResponseFormatter::success($companies->paginate($limit),'Companies Found');
    }

    public function create(CreateCompanyRequest $request)
    {
        try {
            if($request->hasFile('logo')){
                $path = $request->file('logo')->store('public/logos');
            }
    
            $company = Company::create([
                'name' => $request->name,
                'logo' => $path,
            ]);
            
            if(!$company)
            {
                throw new Exception('Company not created');
            }

            //attach company to user
            $user = User::find(Auth::id());
            $user->companies()->attach($company->id);

            //load users at company
            $company->load('users');
    
            return ResponseFormatter::success($company, 'Company created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
        
    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        try {
            $company = Company::find($id);

            if(!$company)
            {
                throw new Exception('Company not created');
            }
            if($request->hasFile('logo')){
                $path = $request->file('logo')->store('public/logos');
            }

            //update company
            $company->update([
                'name' => $request->name,
                'logo' => isset($path) ? $path : $company->logo,
            ]);

            return ResponseFormatter::success($company, 'Company updated');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
