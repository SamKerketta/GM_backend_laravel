<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

if (!function_exists('sendWhatsaapSMS')) {
    function sendWhatsaapSMS($mobileno, $templateid, array $message = [])
    {

        $bearerToken = Config::get("constants.WHATSAPP_TOKEN");
        $numberId    = Config::get("constants.WHATSAPP_NUMBER_ID");
        $url         = Config::get("constants.WHATSAPP_URL");

        # New Code
        if ($templateid == 'payment_reminder') {
            $body = [
                "messaging_product" => "whatsapp",
                "to" => "91$mobileno",
                "type" => "template",
                "template" => [
                    "name" => "payment_reminder",
                    "language" => [
                        "code" => "en_US"
                    ],
                    "components" =>
                    [
                        [                                   // Header Logo
                            "type" => "header",
                            "parameters" => [
                                [
                                    "type" => "image",
                                    "image" => [
                                        // "link" => $hospital->logo,
                                        "link" => "https://yourdomain.com/image.jpg",
                                    ]
                                ]
                            ]
                        ],
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $message['name']
                                ],
                                [
                                    "type" => "text",
                                    "text" => $message['gym_name']
                                ],
                                [
                                    "type" => "text",
                                    "text" => $message['total_due']
                                ],
                                [
                                    "type" => "text",
                                    "text" => $message['for_month']
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }


        $mReqs['msg'] = json_encode($body, true);

        // $this->storeNotification();                                         // 1.1

        $result = Http::withToken("$bearerToken")
            ->post("https://graph.facebook.com/v17.0/$numberId/messages", $body);


        $responseBody = json_decode($result->getBody(), true);
        if (isset($responseBody["error"])) {
            $response = ['response' => false, 'status' => 'failure', 'msg' => $responseBody];
        } else {
            $response = ['response' => true, 'status' => 'success', 'msg' => $responseBody];
        }
        return $response;


        # Old Code
        $result      = Http::withHeaders([

            "Authorization" => "Bearer $bearerToken",
            "contentType" => "application/json"

        ])->post($url . $numberId . "/messages", [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => "+91$mobileno", //<--------------------- here
            "type" => "template",
            "template" => [
                "name" => "$templateid",
                "language" => [
                    "code" => "en_US"
                ],
                "components" =>
                [
                    [                                   // Header Logo
                        "type" => "header",
                        "parameters" => [
                            [
                                "type" => "image",
                                "image" => [
                                    "link" => $hospital->logo
                                ]
                            ]
                        ]
                    ],
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => $this->_REQUESTS->patientName
                            ],
                            [
                                "type" => "text",
                                "text" => $hospital->name
                            ],
                            [
                                "type" => "text",
                                "text" => $this->_REQUESTS->appointmentDate
                            ],
                            [
                                "type" => "text",
                                "text" => $this->_REQUESTS->department
                            ],
                            [
                                "type" => "text",
                                "text" => $this->_REQUESTS->consultantDoctor
                            ],
                            [
                                "type" => "text",
                                "text" => $hospital->contact_no
                            ],
                            [
                                "type" => "text",
                                "text" => $hospital->email
                            ],
                            [
                                "type" => "text",
                                "text" => $hospital->name
                            ],
                            [
                                "type" => "text",
                                "text" => $this->_REQUESTS->mrNo
                            ],
                            [
                                "type" => "text",
                                "text" => $this->_REQUESTS->regNo
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        $responseBody = json_decode($result->getBody(), true);
        if (isset($responseBody["error"])) {
            $response = ['response' => false, 'status' => 'failure', 'msg' => $responseBody];
        } else {
            $response = ['response' => true, 'status' => 'success', 'msg' => $responseBody];
        }

        return $response;
    }
}

if (!function_exists('Whatsapp_Send')) {
    function Whatsapp_Send($mobileno, $templateid, array $message = [])
    {
        // $mobileno = 8797770238;
        // $mobileno = 6387148933;
        // $mobileno = 9031248170;
        // $mobileno = 9153975142;
        // $mobileno = 6201675668;   # Guruji
        $res = sendWhatsaapSMS($mobileno, $templateid, $message);
        return $res;
    }
}
