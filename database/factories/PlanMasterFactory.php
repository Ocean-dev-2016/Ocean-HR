<?php

namespace Database\Factories;

use App\Models\PlanMaster;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanMasterFactory extends Factory
{
    protected $model = PlanMaster::class;

    public function definition()
    {
        static $counter = 1;

        $plans = [
            1 => 'Starter Plan',
            2 => 'Enterprise Plan',
        ];

        // Stop after creating 2 fixed plans
        if (!isset($plans[$counter])) {
            return [];
        }

        $name = $plans[$counter];
        $counter++;

        return [
            'name' => $name,
            'plan_valid_day' => $this->faker->numberBetween(30, 365),
            'max_employee_user_count' => $this->faker->numberBetween(3, 50),
            'plan_type' => $this->faker->randomElement(['general', 'private']),
            'app_right' => implode(',', $this->faker->randomElements(range(1, 50), rand(2, 10))),
            'panel_right' => implode(',', $this->faker->randomElements(range(1, 50), rand(2, 10))),
            'status' => 'active',
            'created_by' => 1,
            'updated_by' => 1,
            'deleted_by' => null,
        ];
    }
}
