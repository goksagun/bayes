<?php

use Faker\Factory;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

		$this->call('ReviewTableSeeder');
	}

}

class ReviewTableSeeder extends Seeder {

	public function run()
	{
		Review::truncate();

		$_start = microtime(true);
		$faker = Factory::create();

//		foreach (range(1, 10) as $index) {
//			$tags[] = $faker->word;
//		}
		$tags = ['spam', 'ham', 'natural'];

		foreach (range(1, 100) as $index) {
			$texts[] = $faker->text();
		}

		foreach (range(1, 1000) as $index) {
			$data[] = [
				'text' => $texts[array_rand($texts)],
//				'text' => $faker->text(),
				'tag' => $tags[array_rand($tags)]
			];
		}

		$inserted = Bayes::multiTrain($data);
		$_end = microtime(true);

		if ($inserted) {
			$this->command->info('Review table seeded in '. ($_end - $_start) .' seconds!');
		}
	}
}
