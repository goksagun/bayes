<?php

use Faker\Factory;

class FakerController extends BaseController {

	protected $faker;

	public function __construct()
	{
		$this->faker = Factory::create();
	}

	/**
	 * @return \Illuminate\View\View
     */
	public function getIndex()
	{
		$text = $this->faker->text();

		return View::make('faker.index', compact('text'));
	}

	public function getGenerate()
	{
		return $this->postGenerate();
	}

	public function postGenerate()
	{
		$text = $this->faker->text();

		if (Request::ajax()) {
			return Response::json(
				[
					'success' => true,
					'data' => $text,
					'alert' => [
						'type' => 'success',
						'message' => "Faker generated successfully.",
					]
				]
			);
		}

		Notification::success("Faker generated successfully.");

		return View::make('faker.index', compact('text'));
	}

}
