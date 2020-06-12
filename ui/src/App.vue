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
<div id="app">
    <nav id="navbar-left" class="nav-pf-vertical nav-pf-vertical-with-sub-menus nav-pf-persistent-secondary panel-group">
        <ul class="list-group panel">
            <router-link tag="li" to="/dashboard" active-class="active" class="list-group-item" id="dashboard-item">
                <a>
                    <span class="fa fa-cube"></span>
                    <span class="list-group-item-value font-13px">{{$t('dashboard.app')}}</span>
                </a>
            </router-link>
            <li class="li-empty"></li>
            <router-link tag="li" to="/settings" active-class="active" class="list-group-item">
                <a>
                    <span class="fa fa-gear"></span>
                    <span class="list-group-item-value">{{$t('settings.title')}}</span>
                </a>
            </router-link>
            <li class="li-empty"></li>
            <router-link tag="li" to="/logs" active-class="active" class="list-group-item">
                <a>
                    <span class="fa fa-list"></span>
                    <span class="list-group-item-value">{{$t('logs.title')}}</span>
                </a>
            </router-link>
            <li class="li-empty"></li>
            <router-link tag="li" to="/about" active-class="active" class="list-group-item">
                <a>
                    <span class="fa fa-info"></span>
                    <span class="list-group-item-value">{{$t('about.title')}}</span>
                </a>
            </router-link>
        </ul>
    </nav>
    <div class="container-fluid container-cards-pf">
        <router-view/>
    </div>
</div>
</template>

<script>
export default {
  name: "App",
  watch: {
    $route: function(val) {
      localStorage.setItem("path", val.path);
    }
  },
  mounted() {
    var path = localStorage.getItem("path") || "/";
    this.$router.push(path).catch(err => {});
  },
  methods: {
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

<style>
.stats-container {
  padding: 20px !important;
  border-width: initial !important;
  border-style: none !important;
  border-color: initial !important;
  -o-border-image: initial !important;
  border-image: initial !important;
}

.stats-count {
  font-size: 26px;
  font-weight: 300;
  margin-right: 10px;
  float: left;
  line-height: 1;
}

.stats-description {
  float: left;
  line-height: 1;
}

.stats-text {
  margin-top: 10px !important;
  display: block;
}

.green {
  color: #3f9c35;
}

.red {
  color: #cc0000;
}

.orange {
  color: #ec7a08;
}

.blue {
    color: #0088ce;
}

.gray {
    color: #72767b;
}

.adjust-top-loader {
  top: 0;
}

.mg-left-sm {
  margin-left: 1rem;
}

.mg-left-xs {
  margin-left: 0.5rem;
}

.mg-right-sm {
  margin-right: 1rem !important;
}

.mg-top-lg {
  margin-top: 3rem;
}

.mg-top-md {
  margin-top: 2rem;
}

.mg-top-sm {
  margin-top: 1rem;
}

.no-mg-bottom {
  margin-bottom: 0;
}

.pad-top-xs {
  padding-top: 0.5rem !important;
}

.legend {
  color: #555555;
  background-color: rgba(255, 255, 255, 0.7);
  font-size: 12px;
  padding: 5px;
  position: absolute;
  right: 3rem;
  text-align: left;
  z-index: 5;
}

.dygraph-label.dygraph-ylabel {
  transform: rotate(-90deg) !important;
  text-align: center;
  padding-left: 15px;
}

.clear {
  clear: both;
}

.bg-green {
    background-color: #3f9c35;
}

.bg-red {
    background-color: #cc0000;
}

.bg-orange {
    background-color: #ec7a08;
}

.bg-blue {
    background-color: #0088ce;
}

.bg-gray {
    background-color: #72767b;
}

.hostname-column {
  display: inline-block;
  max-width: 20rem;
  overflow: hidden;
  text-overflow: ellipsis;
}

.font-13px {
  font-size: 13px;
}

.semi-bold {
  font-weight: 600;
}
</style>