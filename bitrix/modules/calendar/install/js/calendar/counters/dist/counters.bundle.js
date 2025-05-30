/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,ui_counterpanel,main_core_events) {
	'use strict';

	class Counters extends ui_counterpanel.CounterPanel {
	  constructor(options) {
	    super({
	      target: options.countersWrap,
	      items: Counters.getCountersValue(options.counters),
	      multiselect: false
	    });
	    this.search = options.search;
	    this.userId = options.userId;
	    this.counters = options.counters;
	    this.countersWrap = options.countersWrap;
	    this.bindEvents();
	  }
	  bindEvents() {
	    main_core_events.EventEmitter.subscribe('BX.UI.CounterPanel.Item:activate', this.onActivateItem.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.UI.CounterPanel.Item:deactivate', this.onDeactivateItem.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.Main.Filter:apply', this.onFilterApply.bind(this));
	  }
	  onActivateItem(event) {
	    const item = event.getData();
	    const shouldSetPreset = item.id === Counters.TYPE_INVITATION || Counters.checkCounterIsForGroup(item.id);
	    if (shouldSetPreset) {
	      this.search.setPresetInvitation();
	    }
	  }
	  onDeactivateItem() {
	    this.search.resetPreset();
	  }
	  recalculateCounters() {
	    Object.entries(this.counters).forEach(([code, data]) => {
	      const item = this.getItemById(code);
	      item == null ? void 0 : item.updateValue(data.value);
	      item == null ? void 0 : item.updateColor(data.color);
	    });
	  }
	  markCounters() {
	    Object.entries(this.counters).forEach(([code, data]) => {
	      const item = this.getItemById(code);
	      if (item.id === 'invitation' || item.id.includes('calendar_group_invites_')) {
	        this.fields.MEETING_STATUS === 'Q' ? item.activate(false) : item.deactivate(false);
	      }
	    });
	  }
	  setCountersValue(counters) {
	    this.counters = counters;
	    this.recalculateCounters();
	  }
	  onFilterApply() {
	    this.fields = this.search.filter.getFilterFieldsValues();
	    this.markCounters();
	  }
	  static checkCounterIsForGroup(counterName) {
	    return new RegExp(Counters.TYPE_GROUP_INVITATION_TPL).test(counterName);
	  }
	  static getCountersValue(counters) {
	    return Object.entries(counters).map(([code, item]) => {
	      return {
	        id: code,
	        title: Counters.getCountersName(code),
	        value: item.value,
	        color: item.color
	      };
	    });
	  }
	  static getCountersName(type) {
	    if (type === Counters.TYPE_INVITATION || Counters.checkCounterIsForGroup(type)) {
	      return main_core.Loc.getMessage('EC_COUNTER_INVITATION');
	    }
	    return null;
	  }
	  static getCounterNameByGroupId(groupId) {
	    return Counters.TYPE_GROUP_INVITATION_TPL.replace('\\d', groupId);
	  }
	}
	Counters.TYPE_INVITATION = 'invitation';
	Counters.TYPE_GROUP_INVITATION_TPL = 'calendar_group_invites_\\d';

	exports.Counters = Counters;

}((this.BX.Calendar = this.BX.Calendar || {}),BX,BX.UI,BX.Event));
//# sourceMappingURL=counters.bundle.js.map
