<?php
if( isset($_GET["title"]) ){

    $title = $_GET["title"];
    $title = str_replace(" ", "%20", $title);
    $content = file_get_contents("https://animesonlinegames.com/?s=".$title);

    
    $pattern = '/<section class="animeItem">\s+<a\s+(?:[^>]*?\s+)?href=(["])(.*?)\1/';
    preg_match_all($pattern, $content, $match);

    if( empty($match[2]) ){
        echo "Not Found";
        die();
    }

    $content = file_get_contents( array_values($match[2])[0] );
    
    function getDataAnime( $content ){
        // Name
        $pattern = '/<h1> Assistir (.*?)<\/h1>/';
        preg_match_all($pattern, $content, $match);
        $name = $match[1][0];

        // Sinopse
        $pattern = '/<p>(.*?)<\/p>/';
        preg_match_all($pattern, $content, $match);
        $sinopse = $match[1][0];

        // Qtd Episodes
        $pattern = '/(?<=<span>Total de Episódios: <\/span>)(.*?)(?=<\/li>)/';
        preg_match_all($pattern, $content, $match);
        $qtdEp = $match[0][0];

        $dataAnime = [
            "name" => $name,
            "sinopse" => $sinopse,
            "qtdEp" => $qtdEp
        ];

        return $dataAnime;
    }
    $dataAnime = getDataAnime( $content );

    function getDataEpisodes( $content ){
        // Link Episode
        $pattern = '/<section class="episodioItem"[^>]*>\s+<a\s+(?:[^>]*?\s+)?href=(["])(.*?)\1/';
        preg_match_all($pattern, $content, $match);
        $episodesLink = $match[2];
    
        // Name Episode
        $pattern = '/<div class="thumb"[^>]*>\s+<img\s+(?:[^>]*?\s+)?alt=(["])(.*?)\1/';
        preg_match_all($pattern, $content, $match);
        array_shift( $match[2]);
        $episodesName = $match[2];
    
        function nameItems( $link, $name ) {
            $temp = [];
            $temp['nameEpisode'] = $name;
            $temp['linkEpisode'] = $link;
            return $temp;
        }

        $dataEpisodes = array_map("nameItems",$episodesLink, $episodesName);

        // Player Episode ( Max of 5 Episodes ) 
        for( $i = 0; $i < min(5, sizeof($episodesLink) ); $i++ )
        {
            $content = file_get_contents($episodesLink[$i]);
            $pattern = '/<a\s+(?:[^>]*?\s+)?href=(["])(.*?)\1/';
            preg_match_all($pattern, $content, $match);

            $dataEpisodes[$i]["playerEpisode"] = $match[2][13];

        }

        return $dataEpisodes;
    }
    $dataEpisode = getDataEpisodes( $content );

    $resArray = [
        "dataAnime" => $dataAnime,
        "dataEpisode" => $dataEpisode
    ];

    echo json_encode($resArray);
}