if( !window.winp ) {
	window.winp = {};
}

(function($) {

	/**
	 * Condition Editor
	 */
	$.widget("winp.winpConditionEditor", {

		options: {
			filters: null
		},

		_create: function() {
			var self = this;

			this._counter = 0;

			this._$editor = this.element;
			this._$editor.data('winp-api', this);

			this._$filters = this._$editor.find(".winp-filters");
			this._$tmplFilter = this._$editor.find(".winp-filter.winp-template").clone().removeClass("winp-template");

			this._$btnAdd = this._$editor.find(".winp-add-filter");

			this._$btnAdd.click(function() {
				self.addFilter();
				return false;
			});

			this._$editor.on('winp.filters-changed', function() {
				self._checkIsEmpty();
				self._checkIsDeleted();
			});

			this._load();

			this._checkIsEmpty();
		},

		_load: function() {

			if( this.options.filters ) {
				for( var index in this.options.filters ) {
					this.addFilter(this.options.filters[index]);
				}
			}
		},

		_checkIsEmpty: function() {

			if( this.getCount() === 0 ) {
				this._$editor.addClass('winp-empty');
			} else {
				this._$editor.removeClass('winp-empty');
			}
		},

		_checkIsDeleted: function() {

			if( this.getCount() === 0 ) {
				this.markChangeFilters();
			}
		},

		markChangeFilters: function() {
			this._$editor.find("#winp_changed_filters").val(1);
		},

		addFilter: function(data) {
			if( !data ) {
				data = {type: 'showif'};
			}

			var self = this;

			var $filter = this._$tmplFilter.clone();
			this._$filters.append($filter);

			$filter.data('winp-editor', this._$editor);

			this._counter = this._counter + 1;

			$filter.winpConditionFilter({
				index: self._counter,
				type: data.type,
				conditions: data.conditions
			});

			self._$editor.trigger('winp.filters-changed');
			return $filter;
		},

		getData: function() {
			var filters = [];

			this._$filters.find(".winp-filter").each(function() {
				var definition = $(this).winpConditionFilter('getData');
				filters.push(definition);
			});

			return filters;
		},

		getCount: function() {
			return this._$editor.find('.winp-filter:not(.winp-template)').length;
		}
	});

	/**
	 * Condition Filter
	 */
	$.widget("winp.winpConditionFilter", {

		options: {
			type: 'showif',
			conditions: null,
			index: null
		},

		_create: function() {
			var self = this;

			this._counter = 0;
			this._index = this.options.index;

			this._$filter = this.element;
			this._$filter.data('winp-api', this);

			this._$editor = this._$filter.data('winp-editor');
			this._$conditions = this._$filter.find(".winp-conditions");

			this._$tmplCondition = this._$editor.find(".winp-condition.winp-template").clone().removeClass("winp-template");
			this._$tmplScope = this._$editor.find(".winp-scope.winp-template").clone().removeClass("winp-template");

			this._load();

			this._$filter.find(".winp-link-add").click(function() {
				self.addCondition();
				return false;
			});

			this._$filter.find(".btn-remove-filter").click(function() {
				self._$filter.remove();
				self._$editor.trigger('winp.filters-changed');
				return false;
			});

			this._$filter.find(".winp-btn-apply-template").click(function() {
				var templateName = $(".winp-select-template").val();

				if( templateName ) {
					var data = self.getTemplateData(templateName);
					if( data ) {
						self.setFilterData(data);
					}
				}

				return false;
			});

			this._$filter.on('winp.conditions-changed', function() {
				self._checkIsEmpty();
			});
		},

		_load: function() {

			if( !this.options.conditions ) {
				this.addCondition();
			} else {
				this.setFilterData(this.options);
			}
		},

		setFilterData: function(data) {

			this._$filter.find('.winp-condition').remove();

			if( data.conditions ) {
				for( var index in data.conditions ) {
					this.addCondition(data.conditions[index]);
				}
			}

			this._$filter.find(".winp-filter-type").val(data.type);
			this._checkIsEmpty();
		},

		_checkIsEmpty: function() {

			if( this.getCount() === 0 ) {
				this._$filter.addClass('winp-empty');
			} else {
				this._$filter.removeClass('winp-empty');
			}

			this._$conditions.find('.winp-scope').each(function() {
				var count = $(this).find('.winp-condition').length;
				if( count === 0 ) {
					$(this).remove();
				}
			});
		},

		addCondition: function(data, $scope) {
			if( !data ) {
				data = {type: 'condition'};
			}

			if( data.type === 'scope' ) {
				this.addScope(data);
			} else if( data.type === 'condition' && !$scope ) {
				var $scope = this.addScope();
				this.addCondition(data, $scope);
			} else {

				var $condition = this._$tmplCondition.clone();
				$scope.append($condition);

				$condition.data('winp-scope', $scope);
				$condition.data('winp-editor', this._$editor);
				$condition.data('winp-filter', this._$filter);

				this._counter = this._counter + 1;
				data.index = this._index + '_' + this._counter;

				$condition.winpCondition(data);
				this._$filter.trigger('winp.conditions-changed');
			}
		},

		addScope: function(data) {
			if( !data ) {
				data = {};
			}

			var $scope = this._$tmplScope.clone();
			this._$conditions.append($scope);

			if( data && data.conditions ) {
				for( var index in data.conditions ) {
					this.addCondition(data.conditions[index], $scope);
				}
			}

			return $scope;
		},

		getData: function() {
			var scopes = [];

			this._$conditions.find('.winp-scope').each(function() {

				var scope = {
					type: 'scope',
					conditions: []
				};

				scopes.push(scope);

				$(this).find('.winp-condition').each(function() {
					var condition = $(this).winpCondition('getData');
					scope.conditions.push(condition);
				});
			});

			var filterType = this._$filter.find(".winp-filter-type").val();

			return {
				conditions: scopes,
				type: filterType
			};
		},

		getCount: function() {
			return this._$filter.find('.winp-condition').length;
		},

		getTemplateData: function(paramName) {
			if( !window.winp ) {
				return;
			}
			if( !window.winp.templates ) {
				return;
			}

			for( var index in window.winp.templates ) {
				var data = window.winp.templates[index];
				if( data['id'] === paramName ) {
					return data['filter'];
				}
			}

			return false;
		}
	});

	/**
	 * Condition
	 */
	$.widget("winp.winpCondition", {

		options: {
			index: null,
			operator: 'equals'
		},

		_create: function() {
			this._index = this.options.index;

			this._$condition = this.element;
			this._$condition.data('winp-condition', this);

			this._$editor = this._$condition.data('winp-editor');
			this._$filter = this._$condition.data('winp-filter');
			this._$scope = this._$condition.data('winp-scope');

			this._editor = this._$editor.data('winp-api');
			this._filter = this._$filter.data('winp-api');

			this._$hint = this.element.find(".winp-hint");
			this._$hintContent = this.element.find(".winp-hint-content");

			this._$tmplDateControl = this._$editor.find(".winp-date-control.winp-template").clone().removeClass("winp-template");
		},

		_init: function() {
			var self = this;

			this._$condition.find(".winp-param-select").change(function() {
				self.prepareFields();
			});
			self.prepareFields(true);

			// buttons

			this._$condition.find(".winp-btn-remove").click(function() {
				self._editor.markChangeFilters();
				self.remove();
				return false;
			});

			this._$condition.find(".winp-btn-or").click(function() {
				self._editor.markChangeFilters();
				self._filter.addCondition(null, self._$scope);
				return false;
			});

			this._$condition.find(".winp-btn-and").click(function() {
				self._editor.markChangeFilters();
				self._filter.addCondition();
				return false;
			});
		},

		remove: function() {
			this._$condition.remove();
			this._$filter.trigger('winp.conditions-changed');
		},

		getData: function() {

			var currentParam = this._$condition.find(".winp-param-select").val();
			var paramOptions = this.getParamOptions(currentParam);

			var $operator = this._$condition.find(".winp-operator-select");
			var currentOperator = $operator.val();

			var value = null;

			if( 'select' === paramOptions['type'] ) {
				value = this.getSelectValue(paramOptions);
			} else if( 'date' === paramOptions['type'] ) {
				value = this.getDateValue(paramOptions);
			} else if( 'date-between' === paramOptions['type'] ) {
				value = this.getDateBetweenValue(paramOptions);
			} else if( 'integer' === paramOptions['type'] ) {
				value = this.getIntegerValue(paramOptions);
			} else {
				value = this.getTextValue(paramOptions);
			}

			return {
				param: currentParam,
				operator: currentOperator,
				type: paramOptions['type'],
				value: value
			};
		},

		prepareFields: function(isInit) {
			var self = this;

			if( isInit && this.options.param ) {
				this.selectParam(this.options.param);
			}

			var currentParam = this._$condition.find(".winp-param-select").val();
			var paramOptions = this.getParamOptions(currentParam);

			this.setParamHint(paramOptions.description);

			var operators = [];

			if( 'select' === paramOptions['type'] || paramOptions['onlyEquals'] ) {
				operators = ['equals', 'notequal'];
			} else if( 'date' === paramOptions['type'] ) {
				operators = ['equals', 'notequal', 'younger', 'older', 'between'];
			} else if( 'date-between' === paramOptions['type'] ) {
				operators = ['between'];
			} else if( 'integer' === paramOptions['type'] ) {
				operators = ['equals', 'notequal', 'less', 'greater', 'between'];
			} else {
				operators = ['equals', 'notequal', 'contains', 'notcontain'];
			}

			this.setOperators(operators);

			if( isInit && this.options.operator ) {
				this.selectOperator(this.options.operator);
			} else {
				this.selectFirstOperator();
			}

			this.createValueControl(paramOptions, isInit);
		},

		/**
		 * Displays and configures the param hint.
		 */
		setParamHint: function(description) {

			if( description ) {
				this._$hintContent.html(description);
				this._$hint.show();
			} else {
				this._$hint.hide();
			}
		},

		/**
		 * Creates control to specify value.
		 */
		createValueControl: function(paramOptions, isInit) {

			if( 'select' === paramOptions['type'] ) {
				this.createValueAsSelect(paramOptions, isInit);
			} else if( 'date' === paramOptions['type'] ) {
				this.createValueAsDate(paramOptions, isInit);
			} else if( 'date-between' === paramOptions['type'] ) {
				this.createValueAsDateBetween(paramOptions, isInit);
			} else if( 'integer' === paramOptions['type'] ) {
				this.createValueAsInteger(paramOptions, isInit);
			} else {
				this.createValueAsText(paramOptions, isInit);
			}
		},

		// -------------------
		// Select Control
		// -------------------

		/**
		 * Creates the Select control.
		 */
		createValueAsSelect: function(paramOptions, isInit) {
			var self = this;

			var createSelect = function(values) {
				var $select = self.createSelect(values);
				self.insertValueControl($select);
				if( isInit && self.options.value ) {
					self.setSelectValue(self.options.value);
				}
				self._$condition.find(".winp-value").trigger("insert.select");
			};

			if( !paramOptions['values'] ) {
				return;
			}
			if( 'ajax' === paramOptions['values']['type'] ) {

				var $fakeSelect = self.createSelect([
					{
						value: null,
						title: '- loading -'
					}
				]);
				self.insertValueControl($fakeSelect);

				$fakeSelect.attr('disabled', 'disabled');
				$fakeSelect.addClass('winp-fake-select');

				if( isInit && this.options.value ) {
					$fakeSelect.data('value', this.options.value);
				}

				var req = $.ajax({
					url: window.ajaxurl,
					method: 'post',
					data: {
						action: paramOptions['values']['action'],
						snippet_id: $('#post_ID').val(),
						_wpnonce: $('#wbcr_inp_snippet_conditions_metabox_nonce').val()
					},
					dataType: 'json',
					success: function(data) {

						if( data.error ) {
							self.advancedOptions.showError(data.error);
							return;
						} else if( !data.values ) {
							self.advancedOptions.showError(req.responseText);
							return;
						}

						createSelect(data.values);
					},
					error: function() {
						self.advancedOptions.showError('Unexpected error during the ajax request.');
					},
					complete: function() {
						if( $fakeSelect ) {
							$fakeSelect.remove();
						}
						$fakeSelect = null;
					}
				});
			} else {
				createSelect(paramOptions['values']);
			}
		},

		/**
		 * Returns a value for the select control.
		 */
		getSelectValue: function() {
			var $select = this._$condition.find(".winp-value select");

			var value = $select.val();
			if( !value ) {
				value = $select.data('value');
			}
			return value;
		},

		/**
		 * Sets a select value.
		 */
		setSelectValue: function(value) {
			var $select = this._$condition.find(".winp-value select");

			if( $select.hasClass('.winp-fake-select') ) {
				$select.data('value', value);
			} else {
				$select.val(value);
			}
		},

		// -------------------
		// Date Control
		// -------------------

		/**
		 * Creates a control for the input linked with the date.
		 */
		createValueAsDate: function(paramOptions, isInit) {

			var $operator = this._$condition.find(".winp-operator-select");
			var $control = this._$tmplDateControl.clone();

			$operator.change(function() {
				var currentOperator = $operator.val();

				if( 'between' === currentOperator ) {
					$control.addClass('winp-between');
					$control.removeClass('winp-solo');
				} else {
					$control.addClass('winp-solo');
					$control.removeClass('winp-between');
				}

			});

			$operator.change();

			var $radioes = $control.find(".winp-switcher input")
				.attr('name', 'winp_switcher_' + this._index)
				.click(function() {
					var value = $control.find(".winp-switcher input:checked").val();
					if( 'relative' === value ) {
						$control.addClass('winp-relative');
						$control.removeClass('winp-absolute');
					} else {
						$control.addClass('winp-absolute');
						$control.removeClass('winp-relative');
					}
				});

			$control.find(".winp-absolute-date input[type='text']").datepicker({
				format: 'dd.mm.yyyy',
				todayHighlight: true,
				autoclose: true
			});

			this.insertValueControl($control);
			if( isInit && this.options.value ) {
				this.setDateValue(this.options.value);
			}
		},

		/**
		 * Returns a value for the Date control.
		 * @returns {undefined}
		 */
		getDateValue: function() {
			var value = {};

			var $operator = this._$condition.find(".winp-operator-select");
			var currentOperator = $operator.val();

			var $control = this._$condition.find(".winp-value > .winp-date-control");
			var $holder = this._$condition.find(".winp-value > .winp-date-control");

			if( 'between' === currentOperator ) {
				$holder = $holder.find(".winp-between-date");
				value.range = true;

				value.start = {};
				value.end = {};

				if( $control.hasClass('winp-relative') ) {
					$holder = $holder.find(".winp-relative-date");

					value.start.unitsCount = $holder.find(".winp-date-value-start").val();
					value.end.unitsCount = $holder.find(".winp-date-value-end").val();

					value.start.units = $holder.find(".winp-date-start-units").val();
					value.end.units = $holder.find(".winp-date-end-units").val();

					value.start.type = 'relative';
					value.end.type = 'relative';

				} else {
					$holder = $holder.find(".winp-absolute-date");

					value.start = $holder.find(".winp-date-value-start").datepicker('getUTCDate').getTime();
					value.end = $holder.find(".winp-date-value-end").datepicker('getUTCDate').getTime();
					value.end = value.end + (((23 * 60 * 60) + (59 * 60) + 59) * 1000) + 999;
				}

			} else {
				$holder = $holder.find(".winp-solo-date");
				value.range = false;

				if( $control.hasClass('winp-relative') ) {
					$holder = $holder.find(".winp-relative-date");

					value.type = 'relative';
					value.unitsCount = $holder.find(".winp-date-value").val();
					value.units = $holder.find(".winp-date-value-units").val();

				} else {
					$holder = $holder.find(".winp-absolute-date");
					value = $holder.find("input[type='text']").datepicker('getUTCDate').getTime();

					if( 'older' === currentOperator ) {
						value = value + (((23 * 60 * 60) + (59 * 60) + 59) * 1000) + 999;
					}
				}
			}

			return value;
		},

		/**
		 * Sets a select value.
		 */
		setDateValue: function(value) {
			if( !value ) {
				value = {};
			}

			var $holder = this._$condition.find(".winp-value > .winp-date-control");
			var $control = this._$condition.find(".winp-value > .winp-date-control");

			if( value.range ) {

				if( 'relative' === value.start.type ) {
					$holder = $holder.find(".winp-relative-date");

					$holder.find(".winp-date-value-start").val(value.start.unitsCount);
					$holder.find(".winp-date-value-end").val(value.end.unitsCount);
					$holder.find(".winp-date-start-units").val(value.start.units);
					$holder.find(".winp-date-end-units").val(value.end.units);

				} else {
					$holder = $holder.find(".winp-absolute-date");

					var start = new Date(value.start);
					var end = new Date(value.end);

					$holder.find(".winp-date-value-start").datepicker('setUTCDate', start);
					$holder.find(".winp-date-value-end").datepicker('setUTCDate', end);
				}

			} else {

				if( 'relative' === value.type ) {
					$holder = $holder.find(".winp-relative-date");

					$holder.find(".winp-date-value").val(value.unitsCount);
					$holder.find(".winp-date-value-units").val(value.units);

				} else {
					$holder = $holder.find(".winp-absolute-date");

					var date = new Date(value);
					$holder.find(".winp-date-value").datepicker('setUTCDate', date);
				}
			}

			var $relative = $control.find(".winp-switcher input[value=relative]");
			var $absolute = $control.find(".winp-switcher input[value=absolute]");

			if( 'relative' === value.type || (value.start && 'relative' === value.start.type) ) {
				$relative.attr('checked', 'checked');
				$relative.click();
			} else {
				$absolute.attr('checked', 'checked');
				$absolute.click();
			}
		},

		// -------------------
		// Date Between Control
		// -------------------

		/**
		 * Creates a control for the input linked with the date between.
		 */
		createValueAsDateBetween: function(paramOptions, isInit) {
			this._$condition.find('.winp-operator-select').hide();
			var $control = this._$tmplDateControl.clone();
			$control.addClass('winp-between');
			$control.removeClass('winp-solo');
			$control.addClass('winp-absolute');
			$control.removeClass('winp-relative');

			$control.find('.winp-switcher input').attr('name', 'winp_switcher_' + this._index);
			$control.find('.winp-switcher').hide();

			$control.find('.winp-absolute-date input[type=\'text\']').datepicker({
				format: 'dd.mm.yyyy',
				todayHighlight: true,
				autoclose: true
			}).attr('readonly', false);

			this.insertValueControl($control);
			if( isInit && this.options.value ) {
				this.setDateBetweenValue(this.options.value);
			}
		},

		/**
		 * Returns a value for the Date Between control.
		 * @returns {undefined}
		 */
		getDateBetweenValue: function() {
			var value = {};

			var $holder = this._$condition.find(".winp-value > .winp-date-control");

			$holder = $holder.find(".winp-between-date");
			value.range = true;

			value.start = {};
			value.end = {};

			$holder = $holder.find(".winp-absolute-date");

			value.start = $holder.find(".winp-date-value-start").datepicker('getUTCDate').getTime();
			value.end = $holder.find(".winp-date-value-end").datepicker('getUTCDate').getTime();
			value.end = value.end + (((23 * 60 * 60) + (59 * 60) + 59) * 1000) + 999;

			return value;
		},

		/**
		 * Sets a select value.
		 */
		setDateBetweenValue: function(value) {
			if( !value ) {
				value = {};
			}

			var $holder = this._$condition.find(".winp-value > .winp-date-control");
			var $control = this._$condition.find(".winp-value > .winp-date-control");

			$holder = $holder.find(".winp-absolute-date");

			var start = new Date(value.start);
			var end = new Date(value.end);

			$holder.find(".winp-date-value-start").datepicker('setUTCDate', start);
			$holder.find(".winp-date-value-end").datepicker('setUTCDate', end);

			var $absolute = $control.find(".winp-switcher input[value=absolute]");

			$absolute.attr('checked', 'checked');
			$absolute.click();
		},

		// -------------------
		// Integer Control
		// -------------------

		/**
		 * Creates a control for the input linked with the integer.
		 */
		createValueAsInteger: function(paramOptions, isInit) {
			var self = this;

			var $operator = this._$condition.find(".winp-operator-select");

			$operator.on('change', function() {
				var currentOperator = $operator.val();

				var $control;
				if( 'between' === currentOperator ) {
					$control = $("<span><input type='text' class='winp-integer-start' /> and <input type='text' class='winp-integer-end' /></span>");
				} else {
					$control = $("<input type='text' class='winp-integer-solo' /></span>");
				}

				self.insertValueControl($control);
			});

			$operator.change();
			if( isInit && this.options.value ) {
				this.setIntegerValue(this.options.value);
			}
		},

		/**
		 * Returns a value for the Integer control.
		 */
		getIntegerValue: function() {
			var value = {};

			var $operator = this._$condition.find(".winp-operator-select");
			var currentOperator = $operator.val();

			if( 'between' === currentOperator ) {
				value.range = true;
				value.start = this._$condition.find(".winp-integer-start").val();
				value.end = this._$condition.find(".winp-integer-end").val();

			} else {
				value = this._$condition.find(".winp-integer-solo").val();
			}

			return value;
		},

		/**
		 * Sets a value for the Integer control.
		 */
		setIntegerValue: function(value) {
			if( !value ) {
				value = {};
			}

			if( value.range ) {
				this._$condition.find(".winp-integer-start").val(value.start);
				this._$condition.find(".winp-integer-end").val(value.end);
			} else {
				this._$condition.find(".winp-integer-solo").val(value);
			}
		},

		// -------------------
		// Text Control
		// -------------------

		/**
		 * Creates a control for the input linked with the integer.
		 */
		createValueAsText: function(paramOptions, isInit) {

			var $control = $("<input type='text' class='winp-text' /></span>");
			this.insertValueControl($control);
			if( isInit && this.options.value ) {
				this.setTextValue(this.options.value);
			}
		},

		/**
		 * Returns a value for the Text control.
		 * @returns {undefined}
		 */
		getTextValue: function() {
			return this._$condition.find(".winp-text").val();
		},

		/**
		 * Sets a value for the Text control.
		 */
		setTextValue: function(value) {
			this._$condition.find(".winp-text").val(value);
		},

		// -------------------
		// Helper Methods
		// -------------------

		selectParam: function(value) {
			this._$condition.find(".winp-param-select").val(value);
		},

		selectOperator: function(value) {
			this._$condition.find(".winp-operator-select").val(value);
		},

		selectFirstOperator: function() {
			this._$condition.find(".winp-operator-select").prop('selectedIndex', 0);
		},

		setOperators: function(values) {
			var $operator = this._$condition.find(".winp-operator-select");
			$operator.show().off('change');

			$operator.find("option").hide();
			for( var index in values ) {
				$operator.find("option[value='" + values[index] + "']").show();
			}
			var value = $operator.find("option:not(:hidden):eq(0)").val();
			$operator.val(value);
		},

		insertValueControl: function($control) {
			this._$condition.find(".winp-value").html("").append($control);

		},

		getParamOptions: function(paramName) {
			if( !window.winp ) {
				return;
			}
			if( !window.winp.filtersParams ) {
				return;
			}

			for( var index in  window.winp.filtersParams ) {
				var paramOptions = window.winp.filtersParams[index];
				if( paramOptions['id'] === paramName ) {
					return paramOptions;
				}
			}

			return false;
		},

		createSelect: function(values, attrs) {

			var $select = $("<select></select>");
			if( attrs ) {
				$select.attr(attrs);
			}

			for( var index in values ) {
				var item = values[index];
				var $option = '';

				if( typeof index === "string" && isNaN(index) === true ) {
					var $optgroup = $("<optgroup></optgroup>").attr('label', index);

					for( var subindex in item ) {
						var subvalue = item[subindex];
						$option = $("<option></option>").attr('value', subvalue['value']).text(subvalue['title']);
						$optgroup.append($option);
					}
					$select.append($optgroup);
				} else {
					$option = $("<option></option>").attr('value', item['value']).text(item['title']);
					$select.append($option);
				}
			}

			return $select;
		},

		createDataPircker: function() {

			var $control = $('<div class="winp-date-control" data-date="today"></div>');
			var $input = $('<input size="16" type="text" readonly="readonly" />');
			var $icon = $('<i class="fa fa-calendar"></i>');

			$control.append($input);
			$control.append($icon);

			var $datepicker = $input.datepicker({
				autoclose: true,
				format: 'dd/mm/yyyy'
			});

			$control.data('winp-datepicker', $datepicker);

			$icon.click(function() {
				$input.datepicker('show');
			});

			$control.on('changeDate', function(ev) {
				$input.datepicker('hide');
			});

			return $control;
		}
	});

	/**
	 * Visability Options.
	 */
	window.visibilityOptions = {

		init: function() {
			this.initSwitcher();
			this.initSimpleOptions();
			this.initAdvancedOptions();
			this.initDefaultAction();
		},

		initSwitcher: function() {
			var $buttons = $(".winp-options-switcher .btn");

			var selectOptions = function(value) {
				if( !value ) {
					value = $("#winp_visibility_mode").val();
				}

				$buttons.removeClass('active');

				if( 'simple' === value ) {
					$(".winp-options-switcher .btn-btn-simple").addClass('active');
					$("#winp-advanced-visibility-options").hide();
					$("#winp-simple-visibility-options").fadeIn(300);
				} else {
					$(".winp-options-switcher .btn-btn-advanced").addClass('active');
					$("#winp-simple-visibility-options").hide();
					$("#winp-advanced-visibility-options").fadeIn(300);
				}

				$("#winp_visibility_mode").val(value);
			};

			$buttons = $(".winp-options-switcher .btn").click(function() {
				var value = $(this).data('value');
				selectOptions(value);
				return false;
			});

			selectOptions();
		},

		initSimpleOptions: function() {
			$("#winp_relock").change(function() {
				if( $(this).is(":checked") ) {
					$("#onp-sl-relock-options").hide().removeClass('hide').fadeIn();
				} else {
					$("#onp-sl-relock-options").hide();
				}
			});
		},

		initAdvancedOptions: function() {
			var $formPost = $("form#post");
			var $hidden = $("#winp_visibility_filters");
			var $editor = $("#winp-advanced-visability-options");

			// creating an editor
			var json_data = $.parseJSON($hidden.val());
			$editor.winpConditionEditor({
				filters: typeof json_data[0] === 'undefined' ? [] : json_data[0]
			});

			// saves conditions on clicking the button Save
			$formPost.submit(function() {
				var data = $editor.winpConditionEditor("getData");
				var json = JSON.stringify(data);
				$hidden.val(json);

				return true;
			});
		},

		// По выбранному параметру "Insertion location" определяем параметры условия
		changeConditionValue: function() {
			var $editor = $("#winp-advanced-visability-options");
			var $condition = $editor.find('.winp-condition').eq(0);
			switch( $("#wbcr_inp_snippet_location").val() ) {
				case 'before_post':
				case 'before_content':
				case 'before_paragraph':
				case 'after_paragraph':
				case 'after_content':
				case 'after_post':
					$condition.find(".winp-value>select").val('base_sing');
					break;
				case 'before_excerpt':
				case 'after_excerpt':
				case 'between_posts':
				case 'before_posts':
				case 'after_posts':
					$condition.find(".winp-value>select").val('base_arch');
					break;
				default:
					$condition.find(".winp-value>select").val('base_web');
			}
		},

		// "Вешаем" события на три select'a. Если юзер их меняет,
		// то запоминаем это и больше автоматом параметры не меняем
		bindTrigger: function() {
			var $editor = $("#winp-advanced-visability-options");
			var $filter = $editor.find('.winp-filter').eq(0);
			$filter.find('select').change(function() {
				$editor.find("#winp_changed_filters").val(1);
			});
		},

		// Устанавливаем первое условие и "навешиваем" события на элементы
		initDefaultAction: function() {
			var $editor = $("#winp-advanced-visability-options");
			var $condition = null;
			var $select = null;
			var self = this;

			// Если ни одного условия ещё нет, то создаем его
			if( $editor.find("#winp_changed_filters").val() == 0 ) {
				if( $(".winp-filter:not(.winp-template)").length == 0 ) {
					// Генерируем событие нажатия кнопки Add new condition
					$("a.winp-add-filter").trigger('click');
				} else {
					$select = $("select.winp-param-select").eq(0);
				}

				// "Вешаем" событие на последний select, который грузится по ajax'у
				$condition = $editor.find('.winp-condition').eq(0);
				$condition.find(".winp-value").on("insert.select", function() {
					if( $editor.find("#winp_changed_filters").val() == 0 ) {
						if( $select == null ) {
							$select = $("select.winp-param-select").eq(0);
							$select.val('location-some-page').trigger('change');
						}

						self.bindTrigger();
						self.changeConditionValue();
					}
				});
			}

			// Если изменили один из двух параметров (scope или location),
			// то при необходимости параметры условия устанавливаем автоматом
			$("#wbcr_inp_snippet_scope, #wbcr_inp_snippet_location").change(function() {
				if( $editor.find("#winp_changed_filters").val() == 0 && $select != null ) {
					// Если первый параметр условия уже установлен
					if( 'location-some-page' == $select.val() ) {
						if( 'auto' == $("#wbcr_inp_snippet_scope").val() ) {
							self.changeConditionValue();
						} else {
							$condition.find(".winp-value>select").val('base_web');
						}
					} else {
						$select.val('location-some-page').trigger('change');
					}
				}
			});

			$editor.find("select.winp-filter-type").change(function() {
				$editor.find("#winp_changed_filters").val(1);
			});
		}
	};

	$(function() {
		window.visibilityOptions.init();
	});

})(jQuery);