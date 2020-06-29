<!--
#
# Copyright (C) 2020 Nethesis S.r.l.
# http://www.nethesis.it - nethserver@nethesis.it
#
# This script is part of NethServer.
#
# NethServer is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License,
# or any later version.
#
# NethServer is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with NethServer.  If not, see COPYING.
#
-->

<template>
  <div>
    <h2>{{$t('settings.title')}}</h2>
    <div v-if="!isLoaded.config" class="spinner form-spinner-loader mg-left-sm"></div>
    <div v-if="isLoaded.config">
      <!-- URL -->
      <div v-show="loadedStatus" class="alert alert-info compact mg-top-md">
        <span class="pficon pficon-info"></span>
        {{$t('dashboard.app')}} {{$t('settings.is_running_at_this_url')}}:&nbsp;
        <a
          :href="ntopngUrl"
          target="_blank"
        >{{ ntopngUrl }}</a>
      </div>
      <form class="form-horizontal" v-on:submit.prevent="saveSettings()">
        <!-- status -->
        <div class="form-group">
          <label class="col-sm-2 control-label">{{$t('settings.enable_ntopng')}}</label>
          <div class="col-sm-4">
            <toggle-button
              class="min-toggle"
              :width="40"
              :height="20"
              :color="{checked: '#0088ce', unchecked: '#bbbbbb'}"
              :value="config.status"
              :sync="true"
              @change="toggleNtopngStatus()"
            />
          </div>
        </div>
        <!-- interfaces -->
        <div v-show="config.status" class="form-group">
          <label class="col-sm-2 control-label pad-top-xs">{{$t('settings.interfaces')}}</label>
          <div class="col-sm-4">
            <div id="pf-list-standard" class="list-view-pf">
              <div v-for="(network, networkName) in networks" class="list-group-item network">
                <input
                  type="checkbox"
                  v-model="networks[networkName].selected"
                  :id="'ck-' + networkName"
                  class="form-control mg-right-sm"
                />
                <label class="no-mg-bottom" :for="'ck-' + networkName">
                  {{ networkName }}
                  <span
                    :class="['label', 'mg-left-xs', roles.includes(network.props.role) ? 'bg-' + network.props.role : 'bg-gray']"
                  >{{ network.props.role }}</span>
                </label>
              </div>
            </div>
          </div>
        </div>
        <!-- advanced options -->
        <legend v-show="config.status" class="fields-section-header-pf" aria-expanded="true">
          <span
            :class="['fa fa-angle-right field-section-toggle-pf', advancedOptions ? 'fa-angle-down' : '']"
          ></span>
          <a
            class="field-section-toggle-pf"
            @click="toggleAdvancedMode()"
          >{{$t('advanced_options')}}</a>
        </legend>
        <!-- web interface port -->
        <div v-show="advancedOptions && config.status" class="form-group">
          <label class="col-sm-2 control-label">{{$t('settings.web_interface_port')}}</label>
          <div class="col-sm-2">
            <input type="number" min="1" max="65535" v-model="config.port" class="form-control" />
          </div>
        </div>
        <!-- authentication -->
        <div v-show="advancedOptions && config.status" class="form-group">
          <label class="col-sm-2 control-label" for="ck-auth">
            {{$t('settings.enable_auth')}}
            <doc-info
              :placement="'top'"
              :title="$t('settings.login_credentials')"
              :chapter="'ntopng_credentials'"
              :inline="true"
            ></doc-info>
          </label>
          <div class="col-sm-2">
            <input type="checkbox" v-model="config.auth" id="ck-auth" class="form-control" />
          </div>
        </div>
        <!-- save settings -->
        <div class="form-group">
          <label class="col-sm-2 control-label label-loader">
            <div
              v-if="!isLoaded.save"
              class="spinner spinner-sm form-spinner-loader adjust-top-loader"
            ></div>
          </label>
          <span class="col-sm-2">
            <button class="btn btn-primary" type="submit" :disabled="!isLoaded.save">{{$t('save')}}</button>
          </span>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: "Settings",
  mounted() {
    this.getNetworks();
  },
  data() {
    return {
      isLoaded: {
        config: false,
        save: true
      },
      config: {
        status: false,
        port: 0,
        auth: false
      },
      loadedStatus: false,
      networks: {},
      ntopngUrl: "",
      advancedOptions: false,
      roles: ["green", "red", "blue", "orange"]
    };
  },
  methods: {
    getConfig() {
      const context = this;
      context.isLoaded.config = false;
      nethserver.exec(
        ["nethserver-ntopng/settings/read"],
        {
          action: "configuration"
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          const props = success.configuration.props;
          context.config.status = props.status === "enabled";
          context.config.port = parseInt(props.TCPPort);
          context.config.auth = props.Authentication === "enabled";
          context.ntopngUrl =
            "http://" + window.location.hostname + ":" + context.config.port;
          context.loadedStatus = context.config.status;
          const selectedInterfaces = props.Interfaces.split(",");

          for (var networkName in context.networks) {
            if (selectedInterfaces.find(iface => iface === networkName)) {
              context.networks[networkName].selected = true;
            } else {
              context.networks[networkName].selected = false;
            }
          }
          context.isLoaded.config = true;
        },
        function(error) {
          console.error(error);
          context.isLoaded.config = true;
        }
      );
    },
    getNetworks() {
      const context = this;
      nethserver.exec(
        ["nethserver-ntopng/settings/read"],
        {
          action: "networks"
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          context.networks = success.networks;
          context.getConfig();
        },
        function(error) {
          console.error(error);
          context.isLoaded.config = true;
        }
      );
    },
    toggleNtopngStatus() {
      this.config.status = !this.config.status;
    },
    toggleAdvancedMode() {
      this.advancedOptions = !this.advancedOptions;
      this.$forceUpdate();
    },
    getSelectedInterfaces() {
      return Object.values(this.networks)
        .filter(network => network.selected)
        .map(network => network.name)
        .join();
    },
    saveSettings() {
      const context = this;
      context.isLoaded.save = false;

      var validateObj = {
        status: context.config.status ? "enabled" : "disabled",
        TCPPort: context.config.port.toString(),
        Interfaces: context.getSelectedInterfaces(),
        Authentication: context.config.auth ? "enabled" : "disabled"
      };

      nethserver.exec(
        ["nethserver-ntopng/settings/validate"],
        validateObj,
        null,
        function(success) {
          context.validationSuccess(validateObj);
        },
        function(error, data) {
          console.error(error);
          context.isLoaded.save = true;
        }
      );
    },
    validationSuccess(validateObj) {
      const context = this;
      nethserver.notifications.success = context.$i18n.t(
        "settings.settings_update_successful"
      );
      nethserver.notifications.error = context.$i18n.t(
        "settings.settings_update_failed"
      );
      nethserver.exec(
        ["nethserver-ntopng/settings/update"],
        validateObj,
        function(stream) {
          console.info("ntopng-configuration-update", stream);
        },
        function(success) {
          context.isLoaded.save = true;
          context.getConfig();
        },
        function(error) {
          console.error(error);
          context.isLoaded.save = true;
        }
      );
    }
  }
};
</script>

<style scoped>
.mg-left-sm {
  margin-left: 1rem;
}

.label-loader {
  padding-right: 0;
}

.list-group-item.network {
  padding-left: 0;
}

.list-group-item.network:hover {
  background: none;
}
</style>
