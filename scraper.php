<?php
function snake_case($str){
	$str = str_replace('*', '', $str);
	$str = strtolower($str);
	$str = str_replace(' ', '_', $str);

	return $str;
}

// Retrieve CoinMarkepCap Listing using their API
$cmc_listing = json_decode(file_get_contents('https://api.coinmarketcap.com/v2/listings/'), true);

if(!empty($cmc_listing) && isset($cmc_listing['data']) && count($cmc_listing['data']) >= 1)
{
	$dom = new DOMDocument();
	foreach($cmc_listing['data'] as $data)
	{
		echo $data['website_slug'];
		if((file_exists("csv/".$data['website_slug'].".csv") && (time()-filemtime("csv/".$data['website_slug'].".csv")) >= 24*60*60) || !file_exists("csv/".$data['website_slug'].".csv"))
		{
			$csv = "";
			@$dom->loadHTML(file_get_contents('https://coinmarketcap.com/currencies/' . $data['website_slug'] . '/historical-data/?start=20130428&end='.date('Ymd', time())));

			$th = $dom->getElementsByTagName('th');
			$header_size = $th->length;
			for($i=0; $i<$th->length; $i++){
				$csv .= snake_case($th->item($i)->nodeValue);

				if($i+1 < $th->length)
					$csv .= ";";
				else
					$csv .= "\r\n";
			}


			$td = $dom->getElementsByTagName('td');
			for($i=0; $i<$td->length; $i++){
				$value = $td->item($i)->nodeValue;
				$value = str_replace(',', '', $value);
				$csv .= $value;

				if(($i+1)%$header_size == 0){
					$csv .= "\r\n";
				}
				else
					$csv .= ";";
			}

			file_put_contents("csv/".$data['website_slug'].".csv", $csv);
			echo " [DONE]\r\n";
		}
		else
			echo " [SKIPPED]\r\n";
	}
}
?>
