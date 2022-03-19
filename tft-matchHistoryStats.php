<!-- <div class="loading-overlay">
<h4>Loading Stats</h4>
</div> -->
<?php
if ( !empty($_POST['color'])) {
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

$api_key = 'RGAPI-6311686b-8bf6-4f3e-a1f5-df9b6d814803';
$summoner_api_call_url = 'https://na1.api.riotgames.com/tft/summoner/v1/summoners';
$match_api_call_url = 'https://americas.api.riotgames.com/tft/match/v1/matches';
$unit_prefix = 'TFT5_';
$trait_prefix = 'Set5_';
$url = $summoner_api_call_url.'/by-name/'.$summoner_name.'?api_key='.$api_key;


//set up item array
$items_array_raw = 'https://terencepercival.com/tft-tracker/items.json';
$items_array = json_decode(file_get_contents($items_array_raw), true);

// echo '<pre style="color:#000;">';
// print_r($items_array);
// echo '</pre>';

//echo '<br>step 0<br>';
//echo $url;

//create & initialize a curl session
$curl = curl_init();

// set our url with curl_setopt() 
curl_setopt($curl, CURLOPT_URL, "$url");

// return the transfer as a string, also with setopt()
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

// curl_exec() executes the started curl session
// $output contains the output string
$summoner_info_raw = curl_exec($curl);
$summoner_info = json_decode($summoner_info_raw, true);
$summoner_puuid = $summoner_info["puuid"];

// echo '<br>step 1<br>';
// echo '<pre style="color:#000;">';
// var_dump ($summoner_info);

if (!is_null($_POST)) {
	if ($summoner_info_raw == false) {
		'<p>There was an error</p>';
		die();
	}

	if (!empty($summoner_info["status"]["status_code"])) {
		echo '<br>ERROR<p>Status Code '.$summoner_info["status"]["status_code"].' '.$summoner_info["status"]["message"].'</p>';
		die();
	}
}

//Get Match IDs
$url_match_ids = $match_api_call_url.'/by-puuid/'.$summoner_puuid.'/ids?count='.$match_count.'&api_key='.$api_key;
//make it ready to loop through array;
$match_count--;

// set our url with curl_setopt()
curl_setopt($curl, CURLOPT_URL, "$url_match_ids");
$match_ids_raw = curl_exec($curl);
$match_ids = json_decode($match_ids_raw, true);

// echo '<br>step 2<br>';
// echo '<pre style="color:#000;">';
// var_dump ($match_ids);

$winrate_data["Overall Stats"] = ["stats" => ["wins" => 0, "top4" => 0, "games" => 0, "avgFinish" => 0,]];
$i=0;
$game_count = 0;

echo '<div class="main-container hide">';

while ($i <= $match_count ){
	$game_count = $i+1;
	//Make a loop to make call $match_count times
	if (!isset($match_ids[$i])) {
		echo '<p class="error">There was an error retrieving your matches</p>';
		break;
	}
	$match_id = $match_ids[$i];

	$url_indiv_match = $match_api_call_url.'/'.$match_id.'?api_key='.$api_key;

	// set our url with curl_setopt()
	curl_setopt($curl, CURLOPT_URL, "$url_indiv_match");
	$indiv_match_info_raw = curl_exec($curl);
	$indiv_match_info = json_decode($indiv_match_info_raw, true);
	$match_players_game_data = $indiv_match_info["info"]["participants"];
	
	// echo '<br>step 3<br>';
	// echo '<pre style="color:#000;">';
	// var_dump ($indiv_match_info);
	

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
			
			//==================================================================================================================================================================================================//
			//==================================================================================================================================================================================================//
			
			// //Traits
			// foreach ($player["traits"] as $trait) {
			// 	if ($trait["tier_current"] > 0) {
			// 		//Remove set name from trait string
			// 		if (($pos = strpos($trait["name"], "_")) !== FALSE) { 
			// 			$trait_name = substr($trait["name"], $pos+1); 
			// 		}
			// 		//Add to dominant traits array
			// 		if ($trait["num_units"] >= $dominant_trait_num) {
			// 			$dominant_trait_num = $trait["num_units"];
			// 			$dominant_traits_array[$trait_name] = $trait["num_units"];
			// 		}
			// 		//Add to general array
			// 		$traits_array[$trait_name] = $trait["num_units"];
			// 	}
			// }
			// //Sort to make it easy to print out in order later
			// asort($traits_array);
			// //Sort to make it more accurate when verifying the accuracy
			// asort($dominant_traits_array);
			
			// //Validate dominant traits
			// foreach ($dominant_traits_array as $trait => $trait_num) {
			// 	if ($trait_num == $dominant_trait_num) {
			// 		$stat_key .= $trait_num.$trait.' ';
			// 		$comp_key .= $trait.' ';
			// 	}
			// 	else {
			// 		unset ($dominant_traits_array[$trait]);
			// 	}
			// }
			
			// //Add results to winrate array
			// if (array_key_exists($stat_key, $winrate_data)){
			// 	switch(true) {
			// 		case $result == 1:
			// 			$winrate_data[$stat_key]["stats"]["wins"]++;
			// 		case $result <= 4;
			// 			$winrate_data[$stat_key]["stats"]["top4"]++;
			// 		default:
			// 			$winrate_data[$stat_key]["stats"]["games"]++;
			// 			$winrate_data[$stat_key]["stats"]["avgFinish"] += $result;
			// 	}
			// }
			// else {
			// 	$data["comp"] = $dominant_traits_array;
			// 	$data["stats"] = $starter_stat;
			// 	$winrate_data[$stat_key] = $data;
			// 	switch(true) {
			// 		case $result == 1:
			// 			$winrate_data[$stat_key]["stats"]["wins"]++;
			// 		case $result <= 4;
			// 			$winrate_data[$stat_key]["stats"]["top4"]++;
			// 		default:
			// 			$winrate_data[$stat_key]["stats"]["games"]++;
			// 			$winrate_data[$stat_key]["stats"]["avgFinish"] = $result;
			// 	}
			// }
			//==================================================================================================================================================================================================//
			//==================================================================================================================================================================================================//
			
			// =============================Rework==========================================//

			//Traits
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

			//==================================================================================================================================================================================================//
			//==================================================================================================================================================================================================//
			
			echo '<div class="game-block '.$comp_key.'" style="order:'.$game_count.'">';
			// echo '<h2><a href="/tft-match-single?mid='.$indiv_match_info["metadata"]["match_id"].'">Game '.$game_count.'</a></h2>';
			echo '<h2>Game '.$game_count.'</h2>';

			// https://stackoverflow.com/questions/10040291/converting-a-unix-timestamp-to-formatted-date-string

			$timestamp_mil = $indiv_match_info["info"]["game_datetime"];
			$timestamp = $timestamp_mil/1000;
			echo '<p>Date '. date("m-d-Y H:i e T", $timestamp).'</p>';
			// echo $indiv_match_info["info"]["game_length"]
			echo '<p>Length '.gmdate("i:s", $indiv_match_info["info"]["game_length"]).'</p>';
			// Version 11.9.... is set 5 starting point
			echo '<p>Version '.$indiv_match_info["info"]["game_version"].'</p>';
			
			echo '<p>Position '.$result.'</p>';

            //Player Traits
			echo '<div class="trait-container">';
			foreach ($traits_array as $trait => $trait_num) {
				echo '<div class="trait-block">';
				echo '<p class="trait">'.$trait_num.' '.$trait.'</p>';
				echo '</div>';
			}
			
            //close trait-container
			echo '</div>';

            //Unit Container
			echo '<div class="unit-container">';
			foreach ($player["units"] as $unit) {
				echo '<div class="unit-block" style="order:'.$unit["tier"].'">';
				if (($pos = strpos($unit["character_id"], "_")) !== FALSE) { 
					$unit_name = substr($unit["character_id"], $pos+1); 
				}
				$unit["rarity"]++;
				echo '<p class="cost-'.$unit["rarity"].'">'.$unit_name.' '.$unit["tier"].'-star</p>';

				// Item json reference deleted

				if (!empty($unit["items"])) {
					echo '<div class="item-block"><h6>items</h6>';
					foreach ($unit["items"] as $item) {
						$item_array_id = array_search($item, array_column($items_array, 'id'));
						if ($item_array_id === false) {
							echo '<p onclick="toggleTooltip(this)" class="item">'.$item.'</p><p class="tooltip hide">This item id is missing from the database</p>';
						}
						else {
							echo '<p onclick="toggleTooltip(this)" class="item';
							if ($items_array[$item_array_id]["isRadiant"] === true) {
								echo ' radiant-item';
							}
							echo '">'.$items_array[$item_array_id]["name"].'<p class="tooltip hide">'.$items_array[$item_array_id]["description"];
							if ($items_array[$item_array_id]["isElusive"] === true) {
								echo ' Can not be crafted';
							}
							echo '</p>';
						}
					}
					//close item-block
					echo '</div>';
				}
				
				//close unit-block
				echo '</div>';
			}
			
			//close unit-container
			echo '</div>';

			//close game-block
			echo '</div>';
		
		}
		
	}
	
	$i++;
}


// close curl resource to free up system resources
// (deletes the variable made by curl_init)
curl_close($curl);


//=================================================== First Version Calculations ===================================================================================================================//
//==================================================================================================================================================================================================//
// $match_count++;
// $i = 0;
// $overall_first_place_decimal = 0;
// $overall_fourth_place_decimal = 0;
// $overall_avg = 0;

// echo '<div id="stat-block" style="order:0">';
// echo '<div class="trait-stats-container">';

// foreach ($winrate_data as $key => $trait_stats) {
// 	echo '<div class="trait-stats ';
// 	foreach ($trait_stats["comp"] as $trait => $trait_num) {
// 		echo $trait.' ';
// 	}
// 	echo '">';
// 	foreach ($trait_stats["comp"] as $trait => $trait_num) {
// 		echo '<h6 onclick="filterTraits(this.id)" class="trait-filter '.$trait.'" id="'.$trait.'">'.$trait_num.' '.$trait.'</h6>';
// 	}
	
// 	$first_place_decimal = $trait_stats["stats"]["wins"] / $trait_stats["stats"]["games"];
// 	$first_place_percentage = sprintf("%.2f%%", $first_place_decimal * 100);
//     $overall_first_place_decimal += $first_place_decimal;
	
// 	$fourth_place_decimal = $trait_stats["stats"]["top4"] / $trait_stats["stats"]["games"];
// 	$fourth_place_percentage = sprintf("%.2f%%", $fourth_place_decimal * 100);
//     $overall_fourth_place_decimal += $fourth_place_decimal;

// 	$avg_place = number_format(($trait_stats["stats"]["avgFinish"] / $trait_stats["stats"]["games"]), 2);
// 	$overall_avg += $avg_place;
	
// 	echo '<p>'.$avg_place.' Avg Place</p>';
// 	echo '<p>'.$first_place_percentage.' First</p>';
// 	echo '<p>'.$fourth_place_percentage.' Top 4</p>';
// 	echo '<p>'.$trait_stats["stats"]["games"].' Games Played</p>';
// 	//close trait-stats
// 	echo '</div>';
// 	$i++;
// }
// //Overall Stats
// $overall_first_place_decimal = $overall_first_place_decimal/$i;
// $overall_first_place_percentage = sprintf("%.2f%%", $overall_first_place_decimal * 100);

// $overall_fourth_place_decimal = $overall_fourth_place_decimal/$i;
// $overall_fourth_place_percentage = sprintf("%.2f%%", $overall_fourth_place_decimal * 100);

// ?>
<!-- <div onclick="showAllStats()" class="overall-stats">
//     <h6>Overall Stats</h6>
//     <p><?php // echo number_format(($overall_avg/$i), 2);?> Avg Place</p>
//     <p><?php //echo $overall_first_place_percentage;?> First</p>
//     <p><?php //echo $overall_fourth_place_percentage;?> Top 4</p>
//     <p><?php //echo $i;?> Unique Comps</p>
//     <p><?php //echo $match_count;?> Games Played</p>
//     <a onclick="showAllStats()" class="button">Reset Filters</a>
// </div> -->
<?php
// //close trait-stats-container
// echo '</div>';
// //close stat-block
// echo '</div>';
//==================================================================================================================================================================================================//
//==================================================================================================================================================================================================//

//version 2

$match_count++;
$overall_first_place_decimal = 0;
$overall_fourth_place_decimal = 0;
$overall_avg = 0;

echo '<div id="stat-block" style="order:0">';
echo '<div class="trait-stats-container">';

// echo '<pre style="color:#000:"';
// print_r($winrate_data);
// echo '</pre><hr>';

foreach ($winrate_data as $trait => $trait_comps) {

	$trait_total_games = 0;
	$trait_avg = 0;
	$trait_first_place_decimal = 0;
	$trait_fourth_place_decimal = 0;
	$comp_variations = 0;

	if ($trait === "Overall Stats") {
		echo '<div class="overall-stats '.$trait.'">';
		echo '<h6 class="'.$trait.'">'.$trait.'</h6>';
		$overall_first_decimal = $trait_comps["stats"]["wins"] / $trait_comps["stats"]["games"];
		$overall_first_place_percentage = sprintf("%.2f%%", $overall_first_decimal * 100);

		$overall_fourth_decimal = $trait_comps["stats"]["top4"] / $trait_comps["stats"]["games"];
		$overall_fourth_place_percentage = sprintf("%.2f%%", $overall_fourth_decimal * 100);

		$overall_avg_place = number_format(($trait_comps["stats"]["avgFinish"] / $game_count), 2);

		echo '<p>'.$overall_avg_place.' Avg Place</p>';
		echo '<p>'.$overall_first_place_percentage.' First</p>';
		echo '<p>'.$overall_fourth_place_percentage.' Top 4</p>';
		echo '<p>'.$game_count.' Games Played</p>';
		echo '<a onclick="showAllStats()" class="button">Reset Filters</a>';
	}
	else {
		echo '<div class="trait-stats '.$trait.'">';
		foreach ($trait_comps as $trait_comp => $comp_data) {
			
			echo '<div class="sub-comp-stats hide '.$trait_comp.'">';
			echo '<h6 class="'.$trait.' sub-comp">'.$trait_comp.'</h6>';

			$comp_first_place_decimal = $comp_data["stats"]["wins"] / $comp_data["stats"]["games"];
			$comp_first_place_percentage = sprintf("%.2f%%", $comp_first_place_decimal * 100);
			$trait_first_place_decimal += $comp_first_place_decimal;
			
			$comp_fourth_place_decimal = $comp_data["stats"]["top4"] / $comp_data["stats"]["games"];
			$comp_fourth_place_percentage = sprintf("%.2f%%", $comp_fourth_place_decimal * 100);
			$trait_fourth_place_decimal += $comp_fourth_place_decimal;

			$comp_avg_place = number_format(($comp_data["stats"]["avgFinish"] / $comp_data["stats"]["games"]), 2);
			$trait_avg += $comp_avg_place;

			$trait_total_games += $comp_data["stats"]["games"];

			echo '<p>'.$comp_avg_place.' Avg Place</p>';
			echo '<p>'.$comp_first_place_percentage.' First</p>';
			echo '<p>'.$comp_fourth_place_percentage.' Top 4</p>';
			echo '<p>'.$comp_data["stats"]["games"].' Games Played</p>';
			//close sub-comp-stats
			echo '</div>';
			$comp_variations++;
		}
	
		echo '<div class="overall-trait-stats '.$trait.'">';
		echo '<h6 onclick="filterTraits(this.id)" class="trait-filter trait-average '.$trait.'" id="'.$trait.'">'.$trait.'</h6>';

		$trait_first_place_decimal = $trait_first_place_decimal / $comp_variations;
		$trait_first_place_percentage = sprintf("%.2f%%", $trait_first_place_decimal * 100);

		$trait_fourth_place_decimal = $trait_fourth_place_decimal / $comp_variations;
		$trait_fourth_place_percentage = sprintf("%.2f%%", $trait_fourth_place_decimal * 100);

		$trait_avg = number_format(($trait_avg / $comp_variations), 2);

		echo '<p>'.$trait_avg.' Avg Place</p>';
		echo '<p>'.$trait_first_place_percentage.' First</p>';
		echo '<p>'.$trait_fourth_place_percentage.' Top 4</p>';
		echo '<p>'.$comp_variations.' Variations</p>';
		echo '<p>'.$trait_total_games.' Games Played</p>';

		//close overall-trait-stats
		echo '</div>';
	}

	//close trait-stats
	echo '</div>';
}


//close trait-stats-container
echo '</div>';
//close stat-block
echo '</div>';
//==================================================================================================================================================================================================//
//==================================================================================================================================================================================================//
//close main-container
echo '</div>';
?>

<script>
    document.addEventListener("load", function() {
	setTimeout(function(){
        var loading_overlay = document.getElementsByClassName("loading-overlay");
        var main_container = document.getElementsByClassName("main-container");
	    loading_overlay.classList.add("hide");
        main_container.classList.remove("hide");
    }, 1000);
});
document.addEventListener("DOMContentLoaded", function() {
	var loading_overlay = document.getElementsByClassName("loading-overlay");
	loading_overlay.classList.add("hide");
});
</script>

