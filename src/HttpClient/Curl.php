<?php

namespace Framework\HttpClient;

// TODO: ヘッダ情報も含めて返すようにする
class Curl {

  public function get(String $url, Array $headers = null, Array $queryArray = null) {
    if (!is_null($queryArray)) {
      $query = http_build_query($queryArray);
      $url .= "?{$query}";
    }
    $ch = curl_init($url);

    curl_setopt_array($ch, [
      CURLOPT_HTTPHEADER => $this->toHeaderArray($headers ?? []),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPGET => true
    ]);

    $response =  curl_exec($ch);
    curl_close($ch);
    return $response;
  }

  public function post(String $url, Array $headers = null, Array $bodyArray = null, Bool $isJSON = false) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_HTTPHEADER => $this->toHeaderArray($headers ?? []),
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $isJSON ? json_encode($bodyArray ?? []) : \http_build_query($bodyArray ?? []),
      CURLOPT_RETURNTRANSFER => true
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
  }

  private function toHeaderArray(Array $from) {
    $header = [];
    foreach ($from as $key => $value) {
      array_push($header, join(': ', [$key, $value]));
    }
    return $header;
  }

}
