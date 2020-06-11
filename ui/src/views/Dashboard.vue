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
    <h2>{{$t('dashboard.title')}}</h2>
    <!-- status -->
    <h3>{{$t('dashboard.status')}}</h3>
    <div v-if="!isLoaded.config" class="spinner form-spinner-loader mg-left-sm"></div>
    <div v-if="isLoaded.config" class="row">
      <div class="stats-container col-sm-4">
        <span
          :class="['card-pf-utilization-card-details-count stats-count', config.status ? 'pficon pficon-ok' : 'pficon-off']"
          data-toggle="tooltip"
          data-placement="top"
          :title="$t(config.status? 'enabled' : 'disabled')"
        ></span>
        <span class="card-pf-utilization-card-details-description stats-description">
          <span class="card-pf-utilization-card-details-line-2 stats-text">
            {{$t('dashboard.app')}}
            <span v-if="config.status">{{ $t('dashboard.is_enabled') }}</span>
            <span v-else>{{ $t('dashboard.is_disabled') }}</span>
          </span>
        </span>
      </div>
    </div>
    <button
      type="button"
      class="btn btn-lg btn-primary main-button"
      :disabled="!config.status"
      @click="openNtopng()"
    >{{ $t('dashboard.openNtopngApp') }}</button>

    <div class="row">
      <!-- top local hosts -->
      <div class="col-md-6">
        <h3>{{$t('dashboard.top_local_hosts')}}</h3>
        <div v-show="!isLoaded.topLocalHosts">
          <div class="blank-slate-pf padding-30">
            <div class="blank-slate-pf-icon">
              <span class="fa fa-table"></span>
            </div>
            <h4 class="chart-title gray">{{ $t('dashboard.no_data') }}</h4>
          </div>
        </div>
        <vue-good-table
          v-show="isLoaded.topLocalHosts"
          :columns="columns"
          :rows="topLocalHosts"
          :lineNumbers="false"
          :sort-options="{
              enabled: true,
              initialSortBy: {field: 'throughput', type: 'desc'},
            }"
          :search-options="{
              enabled: false
            }"
          :pagination-options="{
              enabled: false
            }"
          styleClass="table responsive vgt2"
        >
          <template slot="table-row" slot-scope="props">
            <span v-if="props.column.field == 'name'" class="hostname-column">
              <span class="semi-bold" :title="props.row.name">{{ props.row.name }}</span>
            </span>
            <span v-else-if="props.column.field == 'ip'">
              <span>{{ props.row.ip }}</span>
            </span>
            <span v-else-if="props.column.field == 'throughput'">
              <span class="semi-bold">{{ props.row.throughput | bpsFormat}}</span>
            </span>
          </template>
        </vue-good-table>
      </div>

      <!-- top remote hosts -->
      <div class="col-md-6">
        <h3>{{$t('dashboard.top_remote_hosts')}}</h3>
        <div v-show="!isLoaded.topRemoteHosts">
          <div class="blank-slate-pf padding-30">
            <div class="blank-slate-pf-icon">
              <span class="fa fa-table"></span>
            </div>
            <h4 class="chart-title gray">{{ $t('dashboard.no_data') }}</h4>
          </div>
        </div>
        <vue-good-table
          v-show="isLoaded.topRemoteHosts"
          :columns="columns"
          :rows="topRemoteHosts"
          :lineNumbers="false"
          :sort-options="{
              enabled: true,
              initialSortBy: {field: 'throughput', type: 'desc'},
            }"
          :search-options="{
              enabled: false
            }"
          :pagination-options="{
              enabled: false
            }"
          styleClass="table responsive vgt2"
        >
          <template slot="table-row" slot-scope="props">
            <span v-if="props.column.field == 'name'" class="hostname-column">
              <span class="semi-bold" :title="props.row.name">{{ props.row.name }}</span>
            </span>
            <span v-else-if="props.column.field == 'ip'">
              <span>{{ props.row.ip }}</span>
            </span>
            <span v-else-if="props.column.field == 'throughput'">
              <span class="semi-bold">{{ props.row.throughput | bpsFormat}}</span>
            </span>
          </template>
        </vue-good-table>
      </div>
    </div>

    <!-- charts -->
    <div class="row mg-top-lg clear">
      <div class="col-md-6">
        <h3 class="col-sm-12">
          {{$t('dashboard.traffic_by_interface')}}
          <div id="traffic-interface-legend" class="legend"></div>
        </h3>
        <div v-show="!isLoaded.trafficByInterface">
          <div class="blank-slate-pf padding-30">
            <div class="blank-slate-pf-icon">
              <span class="fa fa-pie-chart"></span>
            </div>
            <h4 class="chart-title gray">{{ $t('dashboard.no_data') }}</h4>
          </div>
        </div>
        <div id="traffic-interface-chart" class="traffic-chart col-sm-12"></div>
      </div>
      <div class="col-md-6">
        <h3 class="col-sm-12">
          {{$t('dashboard.traffic_by_protocol')}}
          <div id="traffic-protocol-legend" class="legend"></div>
        </h3>
        <div v-show="!isLoaded.trafficByProtocol">
          <div class="blank-slate-pf padding-30">
            <div class="blank-slate-pf-icon">
              <span class="fa fa-pie-chart"></span>
            </div>
            <h4 class="chart-title gray">{{ $t('dashboard.no_data') }}</h4>
          </div>
        </div>
        <div id="traffic-protocol-chart" class="traffic-chart col-sm-12"></div>
      </div>
    </div>
  </div>
</template>

<script>
import Dygraph from "dygraphs";

export default {
  name: "Dashboard",
  props: {},
  mounted() {
    this.getConfig();
  },
  beforeRouteLeave(to, from, next) {
    clearInterval(this.tablesInterval);
    clearInterval(this.trafficByInterfaceInterval);
    clearInterval(this.trafficByProtocolInterval);
    next();
  },
  data() {
    return {
      UPDATE_TABLES_INTERVAL: 5000,
      UPDATE_TRAFFIC_BY_INTERFACE_INTERVAL: 5000,
      UPDATE_TRAFFIC_BY_PROTOCOL_INTERVAL: 5000,
      TRAFFIC_BY_INTERFACE_SAMPLES: 60,
      TRAFFIC_BY_PROTOCOL_SAMPLES: 60,
      isLoaded: {
        config: false,
        topLocalHosts: false,
        topRemoteHosts: false,
        trafficByInterface: false,
        trafficByProtocol: false
      },
      config: {
        status: false,
        port: 0
      },
      ntopngUrl: "",
      topLocalHosts: [],
      topRemoteHosts: [],
      columns: [
        {
          label: this.$i18n.t("dashboard.name"),
          field: "name",
          sortable: true
        },
        {
          label: this.$i18n.t("dashboard.ip_address"),
          field: "ip",
          sortable: true
        },
        {
          label: this.$i18n.t("dashboard.throughput"),
          field: "throughput",
          type: "number",
          sortable: true
        }
      ],
      tableLangsTexts: this.tableLangs(),
      tablesInterval: 0,
      trafficByInterfaceInterval: 0,
      trafficByProtocolInterval: 0,
      trafficByInterface: {},
      trafficByProtocol: {
        labels: ["time"],
        data: []
      }
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
            const props = success.configuration.props;
            context.config.status = props.status === "enabled";
            context.config.port = parseInt(props.TCPPort);
            context.ntopngUrl =
              "http://" + window.location.hostname + ":" + context.config.port;

            if (context.config.status) {
              context.initTables();
              context.initCharts();
            }
          } catch (e) {
            console.error(e);
          }
          context.isLoaded.config = true;
        },
        function(error) {
          console.error(error);
          context.isLoaded.config = true;
        }
      );
    },
    initTables() {
      this.updateHostsTables();
      this.tablesInterval = setInterval(
        this.updateHostsTables,
        this.UPDATE_TABLES_INTERVAL
      );
    },
    updateHostsTables() {
      this.getTopLocalHosts();
      this.getTopRemoteHosts();
    },
    initCharts() {
      this.initTrafficByInterfaceChart();
      this.initTrafficByProtocolChart();
    },
    initTrafficByInterfaceChart() {
      const context = this;
      nethserver.exec(
        ["nethserver-ntopng/dashboard/read"],
        {
          action: "traffic-by-interface"
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          const startTime = success.trafficByInterface.data[0][0];

          for (const t in success.trafficByInterface.data) {
            success.trafficByInterface.data[t][0] = new Date(
              success.trafficByInterface.data[t][0] * 1000
            );

            for (
              let i = 1;
              i < success.trafficByInterface.data[t].length;
              i++
            ) {
              // show througput in kbit/s
              success.trafficByInterface.data[t][i] =
                success.trafficByInterface.data[t][i] / 1000;
            }
          }
          context.trafficByInterface = success.trafficByInterface;

          // zero-fill previous chart data
          context.zeroFillTrafficByInterfaceChart(startTime);

          if (context.trafficByInterface.data.length > 0) {
            context.trafficByInterfaceChart = new Dygraph(
              document.getElementById("traffic-interface-chart"),
              context.trafficByInterface.data,
              {
                fillGraph: true,
                stackedGraph: true,
                labels: context.trafficByInterface.labels,
                height: 200,
                strokeWidth: 1,
                strokeBorderWidth: 1,
                ylabel: context.$i18n.t("dashboard.kbps"),
                axisLineColor: "white",
                labelsDiv: document.getElementById("traffic-interface-legend"),
                labelsSeparateLines: true,
                legend: "always",
                axes: {
                  y: {
                    axisLabelFormatter: function(y) {
                      return Math.ceil(y);
                    }
                  }
                }
              }
            );
            context.trafficByInterfaceChart.initialData =
              context.trafficByInterface.data;
            context.isLoaded.trafficByInterface = true;

            // periodically update data
            if (context.trafficByInterfaceInterval == 0) {
              context.trafficByInterfaceInterval = setInterval(
                context.updateTrafficByInterfaceChart,
                context.UPDATE_TRAFFIC_BY_INTERFACE_INTERVAL
              );
            }
          } else {
            context.isLoaded.trafficByInterface = true;
            context.$forceUpdate();
          }
        },
        function(error) {
          console.error(error);
          context.isLoaded.trafficByInterface = true;
        }
      );
    },
    updateTrafficByInterfaceChart() {
      const context = this;
      nethserver.exec(
        ["nethserver-ntopng/dashboard/read"],
        {
          action: "traffic-by-interface"
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          if (success.trafficByInterface.data.length > 0) {
            for (const t in success.trafficByInterface.data) {
              success.trafficByInterface.data[t][0] = new Date(
                success.trafficByInterface.data[t][0] * 1000
              );

              for (
                let i = 1;
                i < success.trafficByInterface.data[t].length;
                i++
              ) {
                // show througput in kbit/s
                success.trafficByInterface.data[t][i] =
                  success.trafficByInterface.data[t][i] / 1000;
              }
            }

            // append to previous data and retain only visible samples (to avoid memory overload)
            context.trafficByInterface.data = context.trafficByInterface.data
              .concat(success.trafficByInterface.data)
              .slice(-1 * context.TRAFFIC_BY_INTERFACE_SAMPLES);

            context.trafficByInterfaceChart.updateOptions({
              file: context.trafficByInterface.data
            });
          } else {
            context.$forceUpdate();
          }
        },
        function(error) {
          console.error(error);
        }
      );
    },
    zeroFillTrafficByInterfaceChart(startTime) {
      let time = startTime;

      for (let i = 0; i < this.TRAFFIC_BY_INTERFACE_SAMPLES; i++) {
        time -= this.UPDATE_TRAFFIC_BY_INTERFACE_INTERVAL / 1000;
        let zeroSample = [new Date(time * 1000)];

        for (let j = 1; j < this.trafficByInterface.labels.length; j++) {
          zeroSample.push(0);
        }
        this.trafficByInterface.data.unshift(zeroSample);
      }
    },
    zeroFillTrafficByProtocolChart(startTime) {
      let time = startTime;

      for (let i = 0; i < this.TRAFFIC_BY_PROTOCOL_SAMPLES; i++) {
        time -= this.UPDATE_TRAFFIC_BY_PROTOCOL_INTERVAL / 1000;
        let zeroSample = [new Date(time * 1000)];

        for (let j = 1; j < this.trafficByProtocol.labels.length; j++) {
          zeroSample.push(0);
        }
        this.trafficByProtocol.data.unshift(zeroSample);
      }
    },
    initTrafficByProtocolChart() {
      const context = this;
      nethserver.exec(
        ["nethserver-ntopng/dashboard/read"],
        {
          action: "traffic-by-protocol"
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          context.prepareTrafficByProtocolChartData(success.trafficByProtocol);

          // zero-fill previous chart data
          context.zeroFillTrafficByProtocolChart(
            success.trafficByProtocol.time
          );

          if (context.trafficByProtocol.data.length > 0) {
            context.trafficByProtocolChart = new Dygraph(
              document.getElementById("traffic-protocol-chart"),
              context.trafficByProtocol.data,
              {
                fillGraph: true,
                stackedGraph: true,
                labels: context.trafficByProtocol.labels,
                height: 200,
                strokeWidth: 1,
                strokeBorderWidth: 1,
                ylabel: context.$i18n.t("dashboard.kbps"),
                axisLineColor: "white",
                labelsDiv: document.getElementById("traffic-protocol-legend"),
                labelsSeparateLines: true,
                labelsShowZeroValues: false,
                hideOverlayOnMouseOut: true,
                axes: {
                  y: {
                    axisLabelFormatter: function(y) {
                      return Math.ceil(y);
                    }
                  }
                }
              }
            );
            context.trafficByProtocolChart.initialData =
              context.trafficByProtocol.data;
            context.isLoaded.trafficByProtocol = true;

            // periodically update data
            if (context.trafficByProtocolInterval == 0) {
              context.trafficByProtocolInterval = setInterval(
                context.updateTrafficByProtocolChart,
                context.UPDATE_TRAFFIC_BY_PROTOCOL_INTERVAL
              );
            }
          } else {
            context.isLoaded.trafficByProtocol = true;
            context.$forceUpdate();
          }
        },
        function(error) {
          console.error(error);
          context.isLoaded.trafficByProtocol = true;
        }
      );
    },
    updateTrafficByProtocolChart() {
      const context = this;
      nethserver.exec(
        ["nethserver-ntopng/dashboard/read"],
        {
          action: "traffic-by-protocol"
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          context.prepareTrafficByProtocolChartData(success.trafficByProtocol);

          if (context.trafficByProtocol.data.length > 0) {
            // retain only visible samples (to avoid memory overload)
            context.trafficByProtocol.data = context.trafficByProtocol.data.slice(
              -1 * context.TRAFFIC_BY_PROTOCOL_SAMPLES
            );

            context.trafficByProtocolChart.updateOptions({
              file: context.trafficByProtocol.data
            });
          } else {
            context.$forceUpdate();
          }
        },
        function(error) {
          console.error(error);
        }
      );
    },
    createTrafficByProtocolSample(trafficByProtocol) {
      let sampleData = [];
      let time = new Date(trafficByProtocol.time * 1000);
      sampleData.push(time);

      for (let protocol of this.trafficByProtocol.labels) {
        if (trafficByProtocol.traffic[protocol]) {
          // display througput in kbit/s
          sampleData.push(trafficByProtocol.traffic[protocol] / 1000);
        } else {
          if (protocol !== "time") {
            sampleData.push(0);
          }
        }
      }
      return sampleData;
    },
    prepareTrafficByProtocolChartData(trafficByProtocol) {
      // manage newly appeared protocols and create last data sample
      const context = this;
      const lastSampleLabels = Object.keys(trafficByProtocol.traffic);
      let appearedProtocols = lastSampleLabels.filter(
        label => !context.trafficByProtocol.labels.includes(label)
      );

      for (let appearedProtocol of appearedProtocols) {
        // add new protocol label
        context.trafficByProtocol.labels.push(appearedProtocol);

        // zero-fill existing data
        for (const t in context.trafficByProtocol.data) {
          context.trafficByProtocol.data[t].push(0);
        }
      }

      // create and add new traffic data
      let newTrafficData = context.createTrafficByProtocolSample(
        trafficByProtocol
      );
      context.trafficByProtocol.data.push(newTrafficData);
    },
    openNtopng() {
      window.open(this.ntopngUrl, "_blank");
    },
    getTopLocalHosts() {
      const context = this;
      nethserver.exec(
        ["nethserver-ntopng/dashboard/read"],
        {
          action: "top-local-hosts",
          host: window.location.hostname
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          context.topLocalHosts = success.topLocalHosts;
          context.isLoaded.topLocalHosts = true;
        },
        function(error) {
          console.error(error);
          context.isLoaded.topLocalHosts = true;
        }
      );
    },
    getTopRemoteHosts() {
      const context = this;
      nethserver.exec(
        ["nethserver-ntopng/dashboard/read"],
        {
          action: "top-remote-hosts"
        },
        null,
        function(success) {
          try {
            success = JSON.parse(success);
          } catch (e) {
            console.error(e);
          }
          context.topRemoteHosts = success.topRemoteHosts;
          context.isLoaded.topRemoteHosts = true;
        },
        function(error) {
          console.error(error);
          context.isLoaded.topRemoteHosts = true;
        }
      );
    },
    getCurrentPath(route, offset) {
      if (offset) {
        return this.$route.path.split("/")[offset] === route;
      } else {
        return this.$route.path.split("/")[1] === route;
      }
    }
  }
};
</script>

<style scoped>
.main-button {
  display: block;
  margin-top: 1rem;
  margin-bottom: 2rem;
}

.traffic-chart {
  margin-bottom: 3rem;
  width: 95%;
}
</style>
