<?php

if (!function_exists("responseMsg")) {
    function responseMsg($status, $message, $data)
    {
        $response = ['status' => $status, "message" => $message, "data" => $data];
        return response()->json($response, 200);
    }
}
/**
 * | To through Validation Error
 */
if (!function_exists("validationError")) {
    function validationError($validator)
    {
        return response()->json([
            'status'  => false,
            'message' => 'validation error',
            'errors'  => $validator->errors()
        ], 422);
    }
}
