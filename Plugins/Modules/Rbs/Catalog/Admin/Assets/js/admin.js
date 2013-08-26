(function ()
{
	"use strict";

	var app = angular.module('RbsChange');

	/**
	 * Routes and URL definitions.
	 */
	app.config(['$provide', function ($provide)
	{
		$provide.decorator('RbsChange.UrlManager', ['$delegate', function ($delegate)
		{
			$delegate.model('Rbs_Catalog_Product').route('prices', 'Rbs/Catalog/Product/:id/Prices/', 'Rbs/Catalog/Product/product-prices.twig');
			$delegate.model('Rbs_Catalog_Category')
				.route('productcategorizations', 'Rbs/Catalog/Category/:id/ProductCategorization/', 'Rbs/Catalog/Category/products.twig')
				.route('tree', 'Rbs/Catalog/nav/?tn=:id', 'Rbs/Catalog/Category/list.twig');

			$delegate.model('Rbs_Catalog').route('home', 'Rbs/Catalog', { 'redirectTo': 'Rbs/Catalog/Product/'});

			$delegate.routesForLocalizedModels([
				'Rbs_Catalog_Product',
				'Rbs_Catalog_Category',
				'Rbs_Catalog_Attribute'
			]);

			return $delegate;
		}]);

	}]);
})();