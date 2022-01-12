<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller {

    protected $product;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(Request $request) {
        try {
            if ($request->bearerToken() == null) {
                return response()->json(['token_not_found'], Response::HTTP_NOT_FOUND);
            } else {
                if (!$this->product = JWTAuth::parseToken()->authenticate()) {
                    return response()->json(['user_found'], Response::HTTP_NOT_FOUND);
                } else {
                    return response()->json(['user_not_found'], Response::HTTP_NOT_FOUND);
                }
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['user_not_found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function index(Request $request) {
        if (!empty($request->header('platform')) && !empty($request->header('source'))) {
            return response()->json([
                        'success' => true,
                        'message' => 'Get All Products all successfully',
                        'datas' => array(
                            'details' => Product::get(),
                            'data' => $this->product
                        )
                            ], Response::HTTP_OK);
        } else {
            return response()->json([
                        'success' => false,
                        'message' => 'Sorry, source or platform is empty',
                        'datas' => null
                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        if (!empty($request->header('platform')) && !empty($request->header('source'))) {
            $data = $request->only('title', 'origin', 'destination', 'typeTrip', 'description', 'start', 'end');
            $validator = Validator::make($data, [
                        'title' => 'required|string',
                        'origin' => 'required|string',
                        'destination' => 'required|string',
                        'typeTrip' => 'required|string',
                        'description' => 'nullable|string',
                        'start' => 'nullable|date_format:Y-m-d H:i:s',
                        'end' => 'nullable|date_format:Y-m-d H:i:s',
            ]);

            //Send failed response if request is not valid
            if ($validator->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }

            //Request is valid, create new product
            $product = new Product([
                'user_id' => $this->product->id,
                'title' => $request->title,
                'origin' => $request->origin,
                'destination' => $request->destination,
                'typeTrip' => $request->typeTrip,
                'Description' => $request->description,
                'startsShedule' => $request->start,
                'endShedule' => $request->end,
            ]);

            $result = $product->save();

            if ($result) {
                return response()->json([
                            'success' => true,
                            'message' => 'Product Trip created successfully',
                            'datas' => array(
                                'details' => $product,
                                'data' => $this->product
                            )
                                ], Response::HTTP_OK);
            } else {
                return response()->json([
                            'success' => false,
                            'message' => 'Product Trip failed successfully',
                            'datas' => array(
                                'details' => $product,
                                'data' => $this->product
                            )
                                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return response()->json([
                        'success' => false,
                        'message' => 'Sorry, source or platform is empty',
                        'datas' => null
                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) {
        if (!empty($request->header('platform')) && !empty($request->header('source'))) {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                            'success' => false,
                            'message' => 'Sorry, Products Trip not found.',
                            'datas' => null
                                ], Response::HTTP_NOT_FOUND);
            }
            return response()->json([
                        'success' => true,
                        'message' => 'Success get detail Product Trip.',
                        'data' => array(
                            'details' => $product,
                            'data' => $this->product
                        )
                            ], Response::HTTP_OK);
        } else {
            return response()->json([
                        'success' => false,
                        'message' => 'Sorry, source or platform is empty',
                        'datas' => null
                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        if (!empty($request->header('platform')) && !empty($request->header('source'))) {
            $data = $request->only('title', 'origin', 'destination', 'typeTrip', 'description', 'start', 'end');
            $validator = Validator::make($data, [
                        'title' => 'required|string',
                        'origin' => 'required|string',
                        'destination' => 'required|string',
                        'typeTrip' => 'required|string',
                        'description' => 'string|nullable',
                        'start' => 'nullable|date_format:Y-m-d H:i:s',
                        'end' => 'nullable|date_format:Y-m-d H:i:s',
            ]);

            //Send failed response if request is not valid
            if ($validator->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }

            $check_data = Product::find($id);
            //Request is valid, update product
            if (!empty($check_data)) {
                $product = Product::where('id', $id)
                        ->update([
                    'user_id' => $this->product->id,
                    'title' => $request->title,
                    'origin' => $request->origin,
                    'destination' => $request->destination,
                    'typeTrip' => $request->typeTrip,
                    'Description' => empty($request->description) ? $check_data['Description'] : $request->description,
                    'startsShedule' => empty($request->start) ? $check_data['startsShedule'] : $request->start,
                    'endShedule' => empty($request->end) ? $check_data['endShedule'] : $request->end,
                ]);

                if ($product) {
                    return response()->json([
                                'success' => true,
                                'message' => 'Product Trip updated successfully',
                                'data' => $product
                                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                                'success' => false,
                                'message' => 'Product Trip updated failed',
                                'data' => $product
                                    ], Response::HTTP_NOT_ACCEPTABLE);
                }
            } else {
                return response()->json([
                                'success' => false,
                                'message' => 'Product Trip not found',
                                'data' => null
                                    ], Response::HTTP_NOT_FOUND);
            }
        } else {
            return response()->json([
                        'success' => false,
                        'message' => 'Sorry, source or platform is empty',
                        'datas' => null
                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id) {
        if (!empty($request->header('platform')) && !empty($request->header('source'))) {
            $data = Product::destroy($id);

            if ($data) {
                return response()->json([
                            'success' => true,
                            'message' => 'Product Trip deleted successfully',
                            'data' => null
                                ], Response::HTTP_OK);
            } else {
                return response()->json([
                            'success' => false,
                            'message' => 'Product Trip deleted failed',
                            'data' => null
                                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return response()->json([
                        'success' => false,
                        'message' => 'Sorry, source or platform is empty',
                        'datas' => null
                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
