<?php
$memcache = new Memcached();
$memcache->addServer('localhost', 11211);
echo "=============================================================================\n";
echo date("Y-m-d H:i:s"). ": Started\n";
$currencies = ["krw", "usd", "idr", "sgd", "thb"];
$markets = [];
$coingecko_data = json_decode(file_get_contents('https://api.coingecko.com/api/v3/coins/groestlcoin?localization=false&community_data=false&developer_data=false&sparkline=false'), true);
if(is_array($coingecko_data) && !isset($coingecko_data["error"]) && count($coingecko_data["market_data"]["current_price"]) > 0){
	$lastUpdatedTimestamp = explode(".", $coingecko_data["market_data"]["last_updated"]);
	$lastUpdatedTimestamp = (int)(strtotime($lastUpdatedTimestamp[0]).rtrim($lastUpdatedTimestamp[1], 'Z'));
	foreach ($coingecko_data["market_data"]["current_price"] as $key => $currency) {
		if(in_array($key, $currencies)) {
			$market = array(
				"symbol" => "GRS",
				"currencyCode" => strtoupper($key),
				"price" => $currency,
				"marketCap" => $coingecko_data["market_data"]["market_cap"][$key],
				"accTradePrice24h" => $coingecko_data["market_data"]["total_volume"][$key],
				"circulatingSupply" => $coingecko_data["market_data"]["circulating_supply"],
				"maxSupply" => $coingecko_data["market_data"]["total_supply"],
				"provider" => "Groestlcoin",
				"lastUpdatedTimestamp" => $lastUpdatedTimestamp
			);
			$memcache->set("$key", $market);
			array_push($markets, $market);
		}
	}
	$memcache->set("markets", $markets);
} else {
	echo date("Y-m-d H:i:s"). ": API is Down\n";
}
echo date("Y-m-d H:i:s"). ": Done\n";
echo "=============================================================================\n";
?>