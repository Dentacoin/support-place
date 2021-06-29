<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\FrontController;

class NotFoundController extends FrontController {

	/**
     * 404 page view
     */
	public function home($locale=null) {

		$params = array(
			'css' => [
				'404.css'
			],
            'seo_title' => '404 - Page not found',
            'seo_description' => '404 - Page not found',
		);

		return $this->ShowView('404', $params, 404);	
	}

	public function catch($locale=null) {

		return redirect(getLangUrl('page-not-found'));
	}
}