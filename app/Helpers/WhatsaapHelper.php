<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

if (!function_exists('sendWhatsaapSMS')) {
    function sendWhatsaapSMS($mobileno, $templateid, array $message = [])
    {

        $bearerToken = Config::get("constants.WHATSAPP_TOKEN");
        $numberId    = Config::get("constants.WHATSAPP_NUMBER_ID");
        $url         = Config::get("constants.WHATSAPP_URL");
        $appurl      = Config::get("constants.APP_URL");

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
                                        "link" => "http://13.61.12.255:5173/assets/GymPro.png",
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

        if ($templateid == 'payment_success_with_invoice') {
            $body = [
                "messaging_product" => "whatsapp",
                "to" => "91$mobileno",
                "type" => "template",
                "template" => [
                    "name" => "payment_success_with_invoice",
                    "language" => [
                        "code" => "en_US"
                    ],
                    "components" =>
                    [
                        [                                   // Header Logo
                            "type" => "header",
                            "parameters" => [
                                [
                                    "type" => "document",
                                    "document" => [
                                        "link"         => $appurl . '/invoice/' . $message['transaction_id'],
                                        "filename"     => "invoice.pdf",
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
                                    "text" => $message['amount_paid']
                                ],
                                [
                                    "type" => "text",
                                    "text" => $message['payment_for_month']
                                ],
                                [
                                    "type" => "text",
                                    "text" => $message['transaction_date']
                                ],
                                [
                                    "type" => "text",
                                    "text" => $message['gym_name']
                                ],
                                [
                                    "type" => "text",
                                    // "text" => "http://65.0.73.240:8001/invoice/1"
                                    "text" => $appurl . '/invoice/' . $message['transaction_id']
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
    }
}

if (!function_exists('Whatsapp_Send')) {
    function Whatsapp_Send($mobileno, $templateid, array $message = [])
    {
        $mobileno = 8797770238;
        // $mobileno = 7319867430;   # Sam
        // $mobileno = 9031248170;
        // $mobileno = 9153975142;
        // $mobileno = 6201675668;   # Guruji
        $res = sendWhatsaapSMS($mobileno, $templateid, $message);
        return $res;
    }
}
