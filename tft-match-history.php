<?php

if ( !empty($_POST['bot'])) {
	echo 'You have been recognized as a bot';
	die();
}
if ( !empty($_POST)) {
	$summoner_name = filter_var($_REQUEST['summonerName'] , FILTER_SANITIZE_URL);
	str_replace(" ", "" , $summoner_name);
	$match_count = filter_var( $_REQUEST['matchCount'] , FILTER_SANITIZE_URL);
}

if ($match_count<0 || $match_count == null) {
	$match_count=1;
}

$perm_api_key = 'RGAPI-6311686b-8bf6-4f3e-a1f5-df9b6d814803';
$api_key = 'RGAPI-acf99109-fab4-4e11-9688-bfca92a6d1c2';
$summoner_api_call_url = 'https://na1.api.riotgames.com/tft/summoner/v1/summoners';
$match_api_call_url = 'https://americas.api.riotgames.com/tft/match/v1/matches';
$unit_prefix = 'TFT5_';
$trait_prefix = 'Set5_';
$url = $summoner_api_call_url.'/by-name/'.$summoner_name.'?api_key='.$api_key;


//set up item array
$items_array_raw = 'https://terencepercival.com/tft-tracker/items.json';
$items_array = json_decode(file_get_contents($items_array_raw), true);

//create & initialize a curl session
$curl = curl_init();

// set our url with curl_setopt() 
curl_setopt($curl, CURLOPT_URL, "$url");

// return the transfer as a string, also with setopt()
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

// curl_exec() executes the started curl session
// $output contains the output string
$summoner_info = json_decode(curl_exec($curl), true);
$summoner_puuid = $summoner_info["puuid"];

// echo '<br>step 1<br>';
// echo '<pre style="color:#000;">';
// var_dump ($summoner_info);

if (!is_null($_POST)) {
	if ($summoner_info == false) {
        echo json_encode(
            array(
                'errorCode' => "",
                'errorMessage' =>"Couldn't find summoner"
            )
        );
		die();
	}

	if (!empty($summoner_info["status"]["status_code"])) {
        echo json_encode(
            array(
                'errorCode' => $summoner_info["status"]["status_code"],
                'errorMessage' => $summoner_info["status"]["message"]
            )
        );
		die();
	}
}

//Get Match IDs
$url_match_ids = $match_api_call_url.'/by-puuid/'.$summoner_puuid.'/ids?count='.$match_count.'&api_key='.$api_key;
//make it ready to loop through array;
$match_count--;

// set our url with curl_setopt()
curl_setopt($curl, CURLOPT_URL, "$url_match_ids");
$match_ids = json_decode(curl_exec($curl), true);

$winrate_data["Overall Stats"] = ["stats" => ["wins" => 0, "top4" => 0, "games" => 0, "avgFinish" => 0,]];
$all_match_info = [];
$i=0;

while ($i <= $match_count ){

    $url_indiv_match = $match_api_call_url.'/'.$match_ids[$i].'?api_key='.$api_key;

    curl_setopt($curl, CURLOPT_URL, "$url_indiv_match");
	$indiv_match_info = json_decode(curl_exec($curl), true);
    $all_match_info[$i] = $indiv_match_info;

	$match_players_game_data = $indiv_match_info["info"]["participants"];

    foreach ($match_players_game_data as $player) {
		if ($summoner_puuid == $player["puuid"]) {
			
			// Php to get stats into arrays
			
			// general stats
			$result = $player["placement"];
			
			// trait vars
			$trait_name = null;
			$comp_key = null;
			$dominant_trait_num = 0;
			$dominant_traits_array = [];
			$traits_array = [];
			
			// unit vars
			$units_array = [];
			
			// winrate vars
			$temp_data = [];
			$trait_array = [];
			$trait_data = [];
			// $stat_key = '';
			$starter_stat = ["wins" => 0, "top4" => 0, "games" => 0, "avgFinish" => 0,];


            foreach ($player["traits"] as $trait) {
				if ($trait["tier_current"] > 0) {
					//Remove set name from trait string
					if (($pos = strpos($trait["name"], "_")) !== FALSE) { 
						$trait_name = substr($trait["name"], $pos+1); 
					}
					else {
						$trait_name = $trait["name"];
					}
					//Add to dominant traits array
					if ($trait["num_units"] >= $dominant_trait_num) {
						$dominant_trait_num = $trait["num_units"];
						$dominant_traits_array[$trait_name] = $trait["num_units"];
					}
					//Add to general array
					$traits_array[$trait_name] = $trait["num_units"];
				}
			}

			//Sort to make it easy to print out in order later
			asort($traits_array);
			//Sort to make it more accurate when verifying the accuracy
			asort($dominant_traits_array);
			
			//Validate dominant traits
			foreach ($dominant_traits_array as $trait => $trait_num) {
				if ($trait_num == $dominant_trait_num) {
					$trait_array[] = $trait;
					$comp_key .= $trait.' ';
				}
				else {
					unset ($dominant_traits_array[$trait]);
				}
			}

			//Add result to overall stats
			switch(true) {
				case $result == 1:
					$winrate_data["Overall Stats"]["stats"]["wins"]++;
				case $result <= 4;
					$winrate_data["Overall Stats"]["stats"]["top4"]++;
				default:
					$winrate_data["Overall Stats"]["stats"]["games"]++;
					$winrate_data["Overall Stats"]["stats"]["avgFinish"] += $result;
			}
			
			foreach ($trait_array as $trait) {
				//Add results to winrate array
				if (array_key_exists($trait, $winrate_data)){
					
					if (array_key_exists($comp_key, $winrate_data[$trait])){
						switch(true) {
							case $result == 1:
								$winrate_data[$trait][$comp_key]["stats"]["wins"]++;
							case $result <= 4;
								$winrate_data[$trait][$comp_key]["stats"]["top4"]++;
							default:
								$winrate_data[$trait][$comp_key]["stats"]["games"]++;
								$winrate_data[$trait][$comp_key]["stats"]["avgFinish"] += $result;
						}
					}
					else {
						$data["comp"] = $dominant_traits_array;
						$data["stats"] = $starter_stat;
						$winrate_data[$trait][$comp_key] = $data;
						switch(true) {
							case $result == 1:
								$winrate_data[$trait][$comp_key]["stats"]["wins"]++;
							case $result <= 4;
								$winrate_data[$trait][$comp_key]["stats"]["top4"]++;
							default:
								$winrate_data[$trait][$comp_key]["stats"]["games"]++;
								$winrate_data[$trait][$comp_key]["stats"]["avgFinish"] = $result;
						}
					}
				}
				else {
					$data["comp"] = $dominant_traits_array;
					$data["stats"] = $starter_stat;
					$trait_data[$comp_key] = $data;
					$winrate_data[$trait] = $trait_data;
					switch(true) {
						case $result == 1:
							$winrate_data[$trait][$comp_key]["stats"]["wins"]++;
						case $result <= 4;
							$winrate_data[$trait][$comp_key]["stats"]["top4"]++;
						default:
							$winrate_data[$trait][$comp_key]["stats"]["games"]++;
							$winrate_data[$trait][$comp_key]["stats"]["avgFinish"] = $result;
					}
				}

			}
        

            
        }

    }

    $i++;

}





echo json_encode(
    array(
        'summonerId' => $summoner_puuid,
        'matchIds' => $match_ids,
        'winrateData' => $winrate_data,
        'allMatchInfo' => $all_match_info,
        'i' => $i
    )
);

?>