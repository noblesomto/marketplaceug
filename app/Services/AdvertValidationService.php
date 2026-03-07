<?php

namespace App\Services;

use App\Models\AdSetting;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Cache;

/**
 * Dynamic Validation Service for Adverts
 *
 * Builds validation rules based on Category UI Config
 * Ensures fields hidden by UI are not required in validation
 */
class AdvertValidationService
{
    /**
     * Get validation rules for creating/updating an advert
     *
     * @param int $categoryId
     * @param int|null $subcategoryId
     * @param bool $isUpdate - If true, images are optional
     * @param bool $hasTempImages - If true, images are optional (already uploaded)
     * @return array
     */
    public function getRules(int $categoryId, ?int $subcategoryId = null, bool $isUpdate = false, bool $hasTempImages = false): array
    {
        // Get UI config
        $uiConfig = $this->getUIConfig($categoryId, $subcategoryId);

        // Base rules (always required)
        $rules = [
            'ad_title' => 'required|max:75',
            'category' => 'required',
            'subcategory' => 'required',
            'brand' => 'required',
            'state' => 'required',
            'lga' => 'required',
            'description' => 'required|max:3500',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:21000',
        ];

        // ✅ Images required only for create (not for jobs category)
        // Skip image requirement if temp images exist (from previous validation error)
        if (!$isUpdate && !in_array($categoryId, [3, 18]) && !$hasTempImages) {
            $minImages = (int) AdSetting::getValue('min_images', 3);
            $maxImages = (int) AdSetting::getValue('max_images', 8);
            $rules['images'] = "required|array|min:{$minImages}|max:{$maxImages}";
        }

        // Add conditional rules based on UI config
        $rules = array_merge($rules, $this->buildConditionalRules($uiConfig, $categoryId, $subcategoryId));

        return $rules;
    }

    /**
     * Build conditional validation rules based on UI config
     *
     * @param array $uiConfig
     * @param int $categoryId
     * @param int|null $subcategoryId
     * @return array
     */
    protected function buildConditionalRules(array $uiConfig, int $categoryId, ?int $subcategoryId): array
    {
        $rules = [];

        // Check if field is visible (not hidden by UI config)
        $isVisible = function($field) use ($uiConfig) {
            // If field is in hide array, it's not visible
            if (in_array($field, $uiConfig['hide'] ?? [])) {
                return false;
            }

            // ✅ FIX: Financial fields must be EXPLICITLY shown
            // salary/expectedSalary/price are mutually exclusive and category-specific
            // They should ONLY be visible if explicitly in the "show" array
            $financialFields = ['salary', 'expectedSalary', 'price'];
            if (in_array($field, $financialFields)) {
                return in_array($field, $uiConfig['show'] ?? []);
            }

            // For other fields, if not explicitly hidden, consider visible
            return true;
        };

        // Check if field is required by subcategory config
        $isRequired = function($field) use ($uiConfig) {
            return in_array($field, $uiConfig['required'] ?? []);
        };

        // Determine which financial field should be required
        $salaryVisible = $isVisible('salary');
        $expectedSalaryVisible = $isVisible('expectedSalary');
        $priceVisible = $isVisible('price');

        // Salary field (for jobs category)
        if ($salaryVisible) {
            $rules['salary'] = 'required';
        }

        // Expected salary field (for CVs category)
        if ($expectedSalaryVisible) {
            $rules['expected_salary'] = 'required';
        }

        // Price field - required by default unless salary/expectedSalary visible OR contact_price=yes
        if ($priceVisible) {
            // If salary or expectedSalary are shown, price is not required
            if ($salaryVisible || $expectedSalaryVisible) {
                $rules['price'] = 'nullable|numeric';
            } else {
                // Price is required unless user selected "Contact for Price"
                $rules['price']      = 'required_unless:contact_price,yes|nullable|numeric';
                $rules['price_type'] = 'required_unless:contact_price,yes';
            }
        }

        // Quantity field
        if ($isVisible('quantity')) {
            $rules['quantity'] = 'nullable|numeric|min:1';
        }

        // Item condition (only if visible and not excluded by category/subcat)
        if ($isVisible('itemCondition')) {
            $skipItemConditionCategories = [3, 11, 18]; // Jobs, Services, CVs
            $skipItemConditionSubcats = [2, 6, 21, 22, 23, 24, 25]; // Cars, Phones, etc.

            if (!in_array($categoryId, $skipItemConditionCategories) &&
                !in_array($subcategoryId, $skipItemConditionSubcats)) {
                $rules['item_condition'] = 'required';
            }
        }

        // Car-specific fields — always required for car subcategories regardless of UI config.
        // Do NOT gate this on $isVisible('divCar'): UI config is a frontend concern only,
        // and a stale cache / missing config would silently skip all car validation.
        if (in_array($subcategoryId, [2, 21, 23])) {
            $rules['condition']      = 'required';
            $rules['registration']   = 'required';
            $rules['fuel']           = 'required';
            $rules['transmission']   = 'required';
            $rules['vehicle_type']   = 'required';
            $rules['exterior_color'] = 'required';
            // model is an integer FK into the models table — must exist and be non-zero
            $rules['model']          = 'required|numeric|min:1|exists:models,id';
        }

        // Phone-specific fields — always required for phone subcategory
        if ($subcategoryId == 6) {
            $rules['phone_color']     = 'required';
            $rules['phone_condition'] = 'required';
            $rules['device']          = 'required';
            // model dropdown is not shown for phones — no model required
        }

        // Generic model requirement (for other subcategories that declare model required)
        if ($isRequired('model') && !isset($rules['model'])) {
            $rules['model'] = 'required|numeric|min:1|exists:models,id';
        }

        return $rules;
    }

    /**
     * Get merged UI config for category and subcategory
     *
     * @param int $categoryId
     * @param int|null $subcategoryId
     * @return array
     */
    protected function getUIConfig(int $categoryId, ?int $subcategoryId): array
    {
        $config = Cache::remember('category_ui_config_v1', 60 * 60 * 24, function () {
            $categories = Category::select('id', 'ui_config')->get();
            $subcategories = SubCategory::select('id', 'ui_config')->get();

            $catConfig = [];
            foreach ($categories as $cat) {
                if ($cat->ui_config) {
                    $catConfig[$cat->id] = json_decode($cat->ui_config, true);
                }
            }

            $subConfig = [];
            foreach ($subcategories as $sub) {
                if ($sub->ui_config) {
                    $subConfig[$sub->id] = json_decode($sub->ui_config, true);
                }
            }

            return [
                'categories' => $catConfig,
                'subcategories' => $subConfig,
            ];
        });

        // Get category config
        $categoryConfig = $config['categories'][$categoryId] ?? $this->getDefaultCategoryConfig($categoryId);

        // Get subcategory config (if provided)
        $subcategoryConfig = [];
        if ($subcategoryId) {
            $subcategoryConfig = $config['subcategories'][$subcategoryId] ?? [];
        }

        // Merge configs (subcategory rules are additive)
        return $this->mergeConfigs($categoryConfig, $subcategoryConfig);
    }

    /**
     * Merge category and subcategory configs
     * Subcategory rules are ADDITIVE to category rules
     *
     * @param array $categoryConfig
     * @param array $subcategoryConfig
     * @return array
     */
    protected function mergeConfigs(array $categoryConfig, array $subcategoryConfig): array
    {
        $merged = [
            'show' => array_merge(
                $categoryConfig['show'] ?? [],
                $subcategoryConfig['show'] ?? []
            ),
            'hide' => array_merge(
                $categoryConfig['hide'] ?? [],
                $subcategoryConfig['hide'] ?? []
            ),
            'required' => $subcategoryConfig['required'] ?? [],
            'labels' => array_merge(
                $categoryConfig['labels'] ?? [],
                $subcategoryConfig['labels'] ?? []
            ),
        ];

        // Remove duplicates
        $merged['show'] = array_unique($merged['show']);
        $merged['hide'] = array_unique($merged['hide']);

        // If a field is in both show and hide, hide takes precedence
        $merged['show'] = array_diff($merged['show'], $merged['hide']);

        return $merged;
    }

    /**
     * Get default category config (fallback if no custom config)
     *
     * @param int $categoryId
     * @return array
     */
    protected function getDefaultCategoryConfig(int $categoryId): array
    {
        // Default configs for common categories
        $defaults = [
            1 => [ // Jobs
                'show' => ['salary'],
                'hide' => ['price', 'shipment', 'itemCondition', 'buyDirect', 'expectedSalary', 'quantity'],
                'labels' => ['brand' => 'Select Job Type:'],
            ],
            2 => [ // Services
                'show' => ['services'],
                'hide' => ['price', 'salary', 'expectedSalary', 'quantity', 'shipment'],
                'labels' => ['brand' => 'Select Service Type:'],
            ],
            3 => [ // Jobs (duplicate ID? check actual category IDs)
                'show' => ['salary'],
                'hide' => ['price', 'shipment', 'itemCondition', 'buyDirect', 'expectedSalary', 'quantity'],
                'labels' => ['brand' => 'Select Job Type:'],
            ],
            18 => [ // CVs
                'show' => ['expectedSalary'],
                'hide' => ['price', 'salary', 'shipment', 'itemCondition', 'buyDirect', 'quantity'],
                'labels' => ['brand' => 'Select Position:'],
            ],
        ];

        return $defaults[$categoryId] ?? [
            'show' => ['price', 'quantity', 'shipment', 'itemCondition', 'buyDirect'],
            'hide' => ['services', 'salary', 'expectedSalary'],
            'labels' => ['brand' => 'Select Option:'],
        ];
    }

    /**
     * Validate shipping requirements
     *
     * @param \Illuminate\Http\Request $request
     * @return array|null Returns error array if validation fails, null if passes
     */
    public function validateShipping($request): ?array
    {
        if ($request->shipment === 'Ship' && empty($request->input('shipping'))) {
            return ['shipping' => ['Please select at least one shipping method.']];
        }

        return null;
    }
}
