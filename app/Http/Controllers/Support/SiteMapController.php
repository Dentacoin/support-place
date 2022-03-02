<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\FrontController;

use App\Models\SupportQuestion;

class SiteMapController extends FrontController {

	/**
     * sitemap
     */
	public function sitemap($locale=null) {

		$links = [
			getLangUrl('/'),
			getLangUrl('contact'),
		];

		$all_questions = null;

		$curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->api_link.'/get-suppor-info/',
            CURLOPT_SSL_VERIFYPEER => 0
        ));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        if(!empty($resp) && isset($resp->success) && $resp->success) {

        	$all_questions = $resp->data->all_questions;
        }

        if(empty($all_questions)) {
        	$all_questions = SupportQuestion::get();
        }
        
		foreach($all_questions as $q) {

			$links[] = getLangUrl('question/'.$q->slug);
		}

		$content = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
		foreach ($links as $link) {
			$content .= '<url><loc>'.$link.'</loc></url>';
		}
		$content .= '</urlset>';

		return response($content, 200)->header('Content-Type', 'application/xml');
	}
}