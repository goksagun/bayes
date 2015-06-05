<?php
use Faker\Factory;

/**
 * Class ReviewController
 */
class ReviewController extends BaseController {

	/**
	 * @return \Illuminate\View\View
     */
	public function getIndex()
	{
		$reviews = Review::orderBy('id', 'desc')->paginate(15);

		return View::make('reviews.index', compact('reviews'));
	}

	/**
	 * @param $id
	 * @return \Illuminate\View\View
     */
	public function getEdit($id)
	{
		$review = Review::find($id);

		$tags = Review::tags();

		return View::make('reviews.edit', compact('review', 'tags'));
	}

	/**
	 * @param $id
	 * @return \Illuminate\Http\RedirectResponse
     */
	public function postUpdate($id)
	{
		$input = Input::all();

		try {
			if (Bayes::reTrain($id, $input['text'], $input['tag'])) {
				return Redirect::back()->with('success', "Review updated.");
			}

			return Redirect::back()->with('error', "An error occurred, please try again later.");
		} catch (Exception $e) {
			return Redirect::back()->with('error', $e->getMessage());
		}
	}

	/**
	 * @param $id
	 * @return \Illuminate\Http\RedirectResponse
     */
	public function postDestroy($id)
	{
		try {
			if (Bayes::deTrain($id)) {
				return Redirect::back()->with('success', "Review deleted.");
			}

			return Redirect::back()->with('error', "An error occurred, please try again later.");
		} catch (Exception $e) {
			return Redirect::back()->with('error', $e->getMessage());
		}
	}

	public function getTruncate()
	{
		if (Review::truncate()) {
			return Redirect::to('reviews')->with('success', "Review truncated.");
		}

		return Redirect::to('reviews')->with('error', "An error occurred, please try again later.");
	}

	public function getSeed()
	{
		return View::make('reviews.seed');
	}

	public function postSeed()
	{
//		dd(Input::all());

		if (Input::has('truncate')) {
			Review::truncate();
		}

		$_start = microtime(true);
		$faker = Factory::create();

//		foreach (range(1, 10) as $index) {
//			$tags[] = $faker->word;
//		}
		$tags = ['spam', 'ham', 'natural'];

		foreach (range(1, (int)Input::get('text_count', 100)) as $index) {
			$texts[] = $faker->text();
		}

		foreach (range(1, (int)Input::get('data_count', 100)) as $index) {
			$data[] = [
				'text' => $texts[array_rand($texts)],
//				'text' => $faker->text(),
				'tag' => $tags[array_rand($tags)]
			];
		}

//		dd($data);

		$inserted = Bayes::multiTrain($data);
		$_end = microtime(true);

		if ($inserted) {
			return Redirect::to('reviews')->with('success', 'Review table seeded in '. ($_end - $_start) .' seconds!');
		}

		return Redirect::to('reviews')->with('error', "An error occurred, please try again later.");
	}

}
