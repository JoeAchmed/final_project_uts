<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $statusCode = 200;

            // handle params query
            $name = $request['name'];
            $address = $request['address'];
            $status = $request['status'];
            $sort = $request['sort'];
            $order = $request['order'];

            $validOrders = ['asc', 'desc'];
            $validSort = ['tanggal_masuk', 'tanggal_keluar', 'address'];

            $order = in_array(strtolower($order), $validOrders) ? strtolower($order) : '';
            $sort = in_array(strtolower($sort), $validSort) ? strtolower($sort) : '';
            $sort = ($sort === 'tanggal_masuk') ? 'in_date_at' : (($sort === 'tanggal_keluar') ? 'out_date_at' : $sort);

            if ($name) {
                $patients = Patient::where('name', 'LIKE', "%$name%")->get();
            } else if ($address) {
                $patients = Patient::where('address', 'LIKE', "%$address%")->get();
            } else if ($status) {
                $patients = Patient::where('status', 'LIKE', "%$status%")->get();
            } else if ($sort && $order) {
                $patients = Patient::orderBy($sort, $order)->get();
            } else {
                // get all patients
                $patients = Patient::all();
            }

            $result = [
                'message' => 'Success',
                'data' => $patients
            ];


            if ($patients->isEmpty()) {
                $statusCode = 404;
                $result = [
                    'message' => 'Data not found',
                    'data' => []
                ];
            }

            return response()->json($result, $statusCode);
        } catch (\Exception $err) {
            // Handle error
            $errorMessage = $err->getMessage();

            $errorResponse = [
                'message' => 'Error occurred',
                'errors' => $errorMessage,
            ];

            return response()->json($errorResponse, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // create new patient
            $validateData = $request->validate([
                'name' => 'required',
                'phone' => 'required',
                'address' => 'required',
                'status' => 'required|in:positif,sembuh,meninggal',
                'in_date_at' => 'nullable|date',
                'out_date_at' => 'nullable|date',
            ]);

            $patient = Patient::create($validateData);

            $data = [
                'message' => 'Patient is created successfully',
                'data' => $patient
            ];

            return response()->json($data, 201);
        } catch (\Exception $err) {
            // Handle validation error
            $errorMessage = $err->getMessage();

            $errorResponse = [
                'message' => 'Validation Error',
                'errors' => $errorMessage,
            ];

            return response()->json($errorResponse, 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // find patient by id
            $patient = Patient::find($id);
            if (!$patient) {
                return response()->json(['message' => "Patient's data with id $id is not found"], 404);
            }

            $result = [
                'message' => 'Success',
                'data' => $patient
            ];

            return response()->json($result, 200);
        } catch (\Exception $err) {
            // Handle error
            $errorMessage = $err->getMessage();

            $errorResponse = [
                'message' => 'Error occurred',
                'errors' => $errorMessage,
            ];

            return response()->json($errorResponse, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $fields = ['name', 'phone', 'address', 'status', 'in_date_at', 'out_date_at'];

            $atLeastOneFilled = false;

            foreach ($fields as $field) {
                if (!empty($request->input($field))) {
                    $atLeastOneFilled = true;
                    break;
                }
            }

            if (!$atLeastOneFilled) {
                return response()->json(['message' => 'At least one field should be present in the request.'], 400);
            }

            // update patient
            $patient = Patient::find($id);

            if (!$patient) {
                return response()->json(['message' => "Patient's data with id $id is not found"], 404);
            }

            // Validate fields
            $request->validate([
                'name' => 'nullable',
                'phone' => 'nullable',
                'address' => 'nullable',
                'status' => 'nullable|in:positif,sembuh,meninggal',
                'in_date_at' => 'nullable|date',
                'out_date_at' => 'nullable|date',
            ]);

            $patient->update([
                'name' => $request->name ?? $patient->name,
                'phone' => $request->phone ?? $patient->phone,
                'address' => $request->address ?? $patient->address,
                'status' => $request->status ?? $patient->status,
                'in_date_at' => $request->in_date_at ?? $patient->in_date_at,
                'out_date_at' => $request->out_date_at ?? $patient->out_date_at,
            ]);

            $patient->save();

            $result = [
                'message' => "Patient's data with id $id is successfully updated",
                'data' => $patient
            ];

            return response()->json($result, 200);
        } catch (\Exception $err) {
            // Handle validation error
            $errorMessage = $err->getMessage();

            $errorResponse = [
                'message' => 'Validation Error',
                'errors' => $errorMessage,
            ];

            return response()->json($errorResponse, 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // delete patient
            $patient = Patient::find($id);

            if (!$patient) {
                return response()->json(['message' => "Patient's data with id $id is not found"], 404);
            }

            $patient->delete();

            // No Content to send
            return response()->json(null, 204);
        } catch (\Exception $err) {
            // Handle error
            $errorMessage = $err->getMessage();

            $errorResponse = [
                'message' => 'Error occurred',
                'errors' => $errorMessage,
            ];

            return response()->json($errorResponse, 500);
        }
    }
}
