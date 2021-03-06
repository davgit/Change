(function () {

	"use strict";

	var app = angular.module('RbsChange');

	app.service('RbsChange.Models', ['$filter', 'RbsChange.REST', function ($filter, REST) {

		var allModels = [], shouldLoad = true;

		function loadModelsIfNeeded () {
			if (shouldLoad) {
				shouldLoad = false;
				REST.call(REST.getBaseUrl('Rbs/ModelsInfo')).then(function (models) {
					// Copy loaded models into 'allModels', keeping same reference to 'allModels' variable.
					angular.forEach(models, function (model) {
						allModels.push(model);
					});
				});
			}
		}

		// Public API
		return {

			getAll : function () {
				loadModelsIfNeeded();
				return allModels;
			}

		};

	}]);

})();