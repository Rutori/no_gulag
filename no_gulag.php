<?php
const MAX_PHOTOS = 1000;

$token = "--Token--";

$start_time = time();
$API = new API($token);
echo "Gathering info...\n";
$photos = $API->API_request('photos.get',[
    "album_id" => "saved",
    "count" => MAX_PHOTOS
]);
$photos['count'] = $photos['count'] - MAX_PHOTOS;
$offset = MAX_PHOTOS;
while ($photos['count'] > 0) {
    $additional_photos = $API->API_request('photos.get',[
        "album_id" => "saved",
        "count" => MAX_PHOTOS,
        "offset" => $offset
    ]);
    $photos['items'] = array_merge($photos['items'],$additional_photos['items']);
    $photos['count'] = $photos['count'] - MAX_PHOTOS;
    $offset += MAX_PHOTOS;
}
echo "Removal starts...\n";
$counter = count($photos['items']);
foreach ($photos['items'] as $key => $photo) {
    $result = $API->API_request('photos.delete',[
        "owner_id" =>   $photo['owner_id'],
        "photo_id" =>   $photo['id']
    ]);
    if ($result){
        echo $photo['owner_id']."_".$photo['id']." removed\n";
        $counter--;
        echo $counter." remaining\n";        
    } else{
        echo "There was an error deleting ".$photo['owner_id']."_".$photo['id']."\n";
    }
}
echo "Congrats on escaping GULAG\n";
$final_time =  time()-$start_time;
echo "This process took {$final_time} seconds\n";

class API 
{
    private $access_token;
    private $base_url = "https://api.vk.com/method/";
    private $version = "5.80";

    function __construct($token)
    {
       $this->access_token = $token;
    }

    function API_request($method,$args= array()){
        usleep(333334); //максимум три запроса в секунду
        $args['v'] = $this->version;
        $args['access_token'] = $this->access_token;

        $get_data = http_build_query($args);

        $request = $this->base_url.$method."?".$get_data;

        $result_raw = file_get_contents($request);
        $result = json_decode($result_raw,true);
        if (isset($result['response'])){
            return $result['response'];
        } else{
            die(($result['error']['error_msg']."\n"));
        }
    }
}
