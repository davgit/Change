(function ()
{
	"use strict";

	var	app = angular.module('RbsChange'),
		INDEX_FUNCTION_CODE = 'Rbs_Website_Section';

	/**
	 * Controller for list.
	 *
	 * @param $scope
	 * @param Breadcrumb
	 * @param MainMenu
	 * @param i18n
	 * @param Query
	 * @param Loading
	 * @param NotificationCenter
	 * @param Utils
	 * @constructor
	 */
	function ListController($scope, $q, $location, Breadcrumb, MainMenu, i18n, REST, Query, Loading, NotificationCenter, Utils)
	{
		Breadcrumb.resetLocation([
			[i18n.trans('m.rbs.website.admin.js.module-name | ucf'), "Rbs/Website"]
		]);

		function initCurrentSection (section) {
			$scope.currentSection = section;
		}

		var currentPath = $location.path();

		Breadcrumb.ready().then(function (breadcrumbData) {
			initCurrentSection(breadcrumbData.currentNode);
			$scope.$on('Change:TreePathChanged', function (event, breadcrumbData) {
				functionsLoaded = false;
				if (currentPath === $location.path()) {
					initCurrentSection(breadcrumbData.currentNode);
					if ($scope.showFunctions) {
						loadFunctions();
					}
				}
			});
		});

		var functionsLoaded = false;
		$scope.showFunctions = false;
		$scope.loadingFunctions = false;

		function loadFunctions () {
			functionsLoaded = true;
			$scope.loadingFunctions = true;
			$scope.sectionFunctions = [];
			$scope.allFunctions = [];
			Loading.start();
			$q.all([
				REST.action('collectionItems', { 'code': 'Rbs_Website_AvailablePageFunctions' }),
				REST.query(Query.simpleQuery('Rbs_Website_SectionPageFunction', 'section', $scope.currentSection.id), {'column': ['page', 'section', 'functionCode']})
			]).then(
				function (results) {
					$scope.loadingFunctions = false;
					$scope.sectionFunctions = results[1].resources;
					$scope.allFunctions = results[0].items;
					$scope.allFunctions['Rbs_Website_Section'] = {"label": i18n.trans('m.rbs.website.admin.js.index-page | ucf')};
					angular.forEach($scope.sectionFunctions, function (func) {
						func.functionLabel = $scope.allFunctions[func.functionCode].label;
					});
					Loading.stop();
				},
				function (error) {
					$scope.loadingFunctions = false;
					NotificationCenter.error("Fonctions", error);
					Loading.stop();
				}
			);
		}

		$scope.toggleFunctions = function ($event, show) {
			if (angular.isDefined(show)) {
				$scope.showFunctions = show;
			} else {
				$scope.showFunctions = ! $scope.showFunctions;
			}
			if ($scope.showFunctions && (! functionsLoaded || $event.shiftKey)) {
				loadFunctions();
			}
		};

		function reloadList () {
			$scope.$broadcast('Change:DocumentList:DLRbsWebsiteTopicList:call', {'method':'reload'});
		}

		function setListBusy (value) {
			$scope.$broadcast('Change:DocumentList:DLRbsWebsiteTopicList:call', {'method':value?'setBusy':'setNotBusy'});
		}


		//
		// <rbs-document-list/> extensions.
		//

		$scope.listExtend =
		{
			setIndexPage : function (page)
			{
				if (this.isIndexPage(page)) {
					return;
				}

				setListBusy(true);
				Loading.start();
				// Retrieve "index" SectionPageFunction for the current section (if any).
				REST.query(Query.simpleQuery('Rbs_Website_SectionPageFunction', {
					'section' : $scope.currentSection.id,
					'functionCode' : INDEX_FUNCTION_CODE
				}), {'column':['page']}).then(
					// Success
					function (spf)
					{
						// SectionPageFunction exists: set new page on it.
						if (spf.resources.length === 1) {
							spf = spf.resources[0];
							// Nothing to do it the index page is the same.
							if (spf.page.id === page.id) {
								Loading.stop();
								return;
							}
						}
						// SectionPageFunction does NOT exist: create a new one.
						else {
							spf = REST.newResource('Rbs_Website_SectionPageFunction');
							spf.section = $scope.currentSection.id;
							spf.functionCode = INDEX_FUNCTION_CODE;
						}
						spf.page = page.id;
						REST.save(spf).then(
							// Success
							function () {
								setListBusy(false);
								reloadList();
								Loading.stop();
							},
							// Error
							function (error)
							{
								setListBusy(false);
								Loading.stop();
								NotificationCenter.error("Erreur page d'index", error);
							}
						);
					},
					// Error
					function (error)
					{
						setListBusy(false);
						Loading.stop();
						NotificationCenter.error("Erreur page d'index", error);
					}
				);
			},

			isIndexPage : function (page)
			{
				return page.functions && page.functions.indexOf(INDEX_FUNCTION_CODE) !== -1;
			},

			getDocumentErrors : function (doc)
			{
				if (! Utils.isModel(doc, 'Rbs_Website_StaticPage')) {
					return null;
				}
				if (this.isIndexPage(doc) && ! Utils.hasStatus(doc, 'PUBLISHABLE')) {
					return [
						"UNPUBLISHED_INDEX_PAGE_"
					];
				}
				return null;
			}
		};

		MainMenu.loadModuleMenu('Rbs_Website');
	}

	ListController.$inject = [
		'$scope', '$q', '$location',
		'RbsChange.Breadcrumb', 'RbsChange.MainMenu', 'RbsChange.i18n', 'RbsChange.REST', 'RbsChange.Query',
		'RbsChange.Loading', 'RbsChange.NotificationCenter', 'RbsChange.Utils'
	];
	app.controller('Rbs_Website_Topic_ListController', ListController);

	/**
	 * Controller for permission.
	 *
	 * @param $scope
	 * @param $routeParams
	 * @param $location
	 * @param REST
	 * @param i18n
	 * @param $http
	 * @param ArrayUtils
	 * @param MainMenu
	 * @param Breadcrumb
	 * @param $q
	 * @constructor
	 */
	function PermissionController($scope, $routeParams, $location, REST, i18n, $http, ArrayUtils, MainMenu, Breadcrumb, $q)
	{
		$scope.reloadPermissions = function (sectionId, websiteId){
			var url = REST.getBaseUrl('Rbs/Website/SectionPermissionRules?sectionId=' + sectionId + '&websiteId=' + websiteId);
			$http.get(url).success(function (data){
				if (data.userIds.length > 0)
				{
					REST.query({
						model: 'Rbs_User_User',
						where: {
							and: [
								{
									op: 'in',
									lexp: {
										property: 'id'
									},
									rexp: data.userIds
								}
							]
						}
					}).then(function (data){
							$scope.document.users = data.resources;
						});
				}

				if (data.groupIds.length > 0)
				{
					REST.query({
						model: 'Rbs_User_Group',
						where: {
							and: [
								{
									op: 'in',
									lexp: {
										property: 'id'
									},
									rexp: data.groupIds
								}
							]
						}
					}).then(function (data){
							$scope.document.groups = data.resources;
						});
				}
				$scope.restrict = data.userIds.length > 0 || data.groupIds.length > 0;
			});
		};

		$scope.changeRestrict = function (){ $scope.restrict = !$scope.restrict; };

		REST.resource($routeParams.id).then(function (section){
			$scope.document = section;
			Breadcrumb.setPath([[section.label, section.url()], 'Permissions']);
			Breadcrumb.setResource(null);
			var sectionId = $scope.document.id;
			var websiteId = $scope.document.model == 'Rbs_Website_Website' ? $scope.document.id : $scope.document.website.id;
			$scope.reloadPermissions(sectionId, websiteId);
		});

		$scope.updatePermissionRules = function (){

			var url = REST.getBaseUrl('Rbs/Website/UpdateSectionPermissionRules');
			var sectionId = $scope.document.id;
			var websiteId = $scope.document.model == 'Rbs_Website_Website' ? $scope.document.id : $scope.document.website.id;
			var restrictions = false;
			if ($scope.restrict)
			{
				restrictions = {
					users: $scope.document.users,
					groups: $scope.document.groups
				};
			}
			var params = {
				sectionId: sectionId,
				websiteId: websiteId,
				restrictions: restrictions
			};

			$http.post(url, params).success(function(){
				$scope.reloadPermissions(sectionId, websiteId);
			});
		};

		MainMenu.loadModuleMenu('Rbs_Website');
	}

	PermissionController.$inject = ['$scope', '$routeParams', '$location', 'RbsChange.REST', 'RbsChange.i18n', '$http', 'RbsChange.ArrayUtils', 'RbsChange.MainMenu', 'RbsChange.Breadcrumb', '$q'];
	app.controller('Rbs_Website_Topic_PermissionController', PermissionController);
})();