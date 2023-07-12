<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Indonesia;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

const PAGINATION = 10;

class DesaController extends Controller
{
    public function index() {
        $data = Indonesia::paginateVillages(PAGINATION);

        return response()->json($data, 200);
    }

    public function show(int $id) {
        $villageId = $id;
        $data = Indonesia::findVillage($villageId, $with = null);

        return response()->json($data, 200);
    }

    public function create(Request $request) {
        try {
            $validated = $request->validate([
                'district_code' => 'required|max:7',
                'pos'           => 'required|digits:5',
                'lat'           => 'nullable',
                'long'          => 'nullable',
                'name'          => 'required|max:255'
            ]);
    
        } catch (ValidationException $e) {
            // Handle the validation errors
            return response()->json([
                'message' => 'error validations',
                'errors' => $e->errors()
            ], 422);
        }

        $checkDistrict = District::where('code', $validated['district_code'])
                        ->first();
        
        if (!$checkDistrict) {
            return response()->json([
                'message' => 'district code is not valid'
            ], 500);
        }
        
        $lastVillages = Village::select('code')
                        ->where('district_code', $validated['district_code'])
                        ->latest('id')
                        ->first() ?? 0;

        $meta = ([
            'lat'  => $validated['lat'] ?? null,
            'long' => $validated['long'] ?? null,
            'pos'  => $validated['pos'] ?? null
        ]);
        
        $data = [
            'name'          => $validated['name'],
            'code'          => (int) $lastVillages['code'] + 1,
            'district_code' => $validated['district_code'],
            'meta'          => $meta,
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now()
        ];

        DB::beginTransaction();
        
        try {
            Village::create($data);

            DB::commit();

            return response()->json([
                'message' => 'success create data'
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message'   => 'faled create data'
            ], 500);
        }
        
        return $data;
    }

    public function update(Request $request, $id) {
        try {
            $validated = $request->validate([
                'pos'           => 'required|digits:5',
                'lat'           => 'nullable',
                'long'          => 'nullable',
                'name'          => 'required|max:255'
            ]);

        } catch (ValidationException $e) {
            // Handle the validation errors
            return response()->json([
                'message' => 'error validations',
                'errors' => $e->errors()
            ], 422);
        }

        $village = Village::where('id', $id)->first();

        $meta = ([
            'lat'  => $validated['lat'] ?? $village['meta']['lat'],
            'long' => $validated['long'] ?? $village['meta']['long'],
            'pos'  => $validated['pos'] ?? $village['meta']['pos']
        ]);
        
        $data = [
            'name'          => $validated['name'] ?? $village['name'],
            'meta'          => $meta,
            'updated_at'    => Carbon::now()
        ];

        DB::beginTransaction();

        try {
            $village->update($data);

            DB::commit();

            return response()->json([
                'message' => 'success update data'
            ], 200);
        } catch (\Throwable $th) {
            
            DB::rollBack();

            return response()->json([
                'message'   => 'faled update data'
            ], 500);
        }
    }

    public function destroy(int $id) {
        Village::where('id', $id)->delete();

        return response()->json([
            'message'   => 'success delete data'
        ], 200);
    }
}
