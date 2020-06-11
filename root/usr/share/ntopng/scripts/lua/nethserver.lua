--
-- (C) 2013-20 - ntop.org
--

local dirs = ntop.getDirs()
package.path = dirs.installdir .. "/scripts/lua/modules/?.lua;" .. package.path
require "lua_utils"
local discover = require "discover_utils"
local custom_column_utils = require "custom_column_utils"
local format_utils = require "format_utils"
local json = require "dkjson"
local have_nedge = ntop.isnEdge()

sendHTTPContentTypeHeader('text/html')

-- Table parameters
local all = _GET["all"]
local currentPage = _GET["currentPage"]
local perPage     = _GET["perPage"]
local sortColumn  = _GET["sortColumn"]
local sortOrder   = _GET["sortOrder"]
local protocol    = _GET["protocol"]
local long_names  = _GET["long_names"]
local custom_column = _GET["custom_column"]
local traffic_type = _GET["traffic_type"]

-- Host comparison parameters
local mode        = _GET["mode"]
local tracked     = _GET["tracked"]
local ipversion   = _GET["version"]

-- Used when filtering by ASn, VLAN or network
local asn          = _GET["asn"]
local vlan         = _GET["vlan"]
local network      = _GET["network"]
local cidr         = _GET["network_cidr"]
local pool         = _GET["pool"]
local country      = _GET["country"]
local os_          = tonumber(_GET["os"])
local mac          = _GET["mac"]
local top_hidden   = ternary(_GET["top_hidden"] == "1", true, nil)

function update_host_name(h)
   if(h["name"] == nil) then
      if(h["ip"] ~= nil) then
         
         h["name"] = host2name(h["ip"])
      else
	 h["name"] = h["mac"]
      end
   end

   return(h["name"])
end

-- Get from redis the throughput type bps or pps
local throughput_type = 'bps'

if(long_names == nil) then
   long_names = false
else
   if(long_names == "1") then
      long_names = true
   else
      long_names = false
   end
end

local sortPrefs = "hosts"

if((sortColumn == nil) or (sortColumn == "column_"))then
   sortColumn = getDefaultTableSort(sortPrefs)
else
   if((sortColumn ~= "column_")
      and (sortColumn ~= "")) then
      tablePreferences("sort_"..sortPrefs,sortColumn)
   end
end

if(sortOrder == nil) then
   sortOrder = getDefaultTableSortOrder(sortPrefs)
else
   if((sortColumn ~= "column_")
      and (sortColumn ~= "")) then
      tablePreferences("sort_order_"..sortPrefs,sortOrder)
   end
end

if(currentPage == nil) then
   currentPage = 1
else
   currentPage = tonumber(currentPage)
end

if(perPage == nil) then
   perPage = getDefaultTableSize()
else
   perPage = tonumber(perPage)
   tablePreferences("rows_number",perPage)
end

local custom_column_key, custom_column_format
local traffic_type_filter

if traffic_type == "one_way" then
   traffic_type_filter = 1 -- ntop_typedefs.h TrafficType traffic_type_one_way
elseif traffic_type == "bidirectional" then
   traffic_type_filter = 2 -- ntop_typedefs.h TrafficType traffic_type_bidirectional
end

if(tracked ~= nil) then tracked = tonumber(tracked) else tracked = 0 end

if((mode == nil) or (mode == "")) then mode = "all" end

interface.select('view:all')

local to_skip = (currentPage-1) * perPage

if(sortOrder == "desc") then sOrder = false else sOrder = true end

local filtered_hosts = false
local blacklisted = false
local anomalous = false
local dhcp_hosts = false

local hosts_retrv_function = interface.getHostsInfo
if mode == "local" then
   hosts_retrv_function = interface.getLocalHostsInfo
elseif mode == "remote" then
   hosts_retrv_function = interface.getRemoteHostsInfo
elseif mode == "broadcast_domain" then
   hosts_retrv_function = interface.getBroadcastDomainHostsInfo
elseif mode == "filtered" then
   filtered_hosts = true
elseif mode == "blacklisted" then
   blacklisted_hosts = true
elseif mode == "dhcp" then
   dhcp_hosts = true
end

local hosts_stats = hosts_retrv_function(false, sortColumn, perPage, to_skip, sOrder,
					 country, os_, tonumber(vlan), tonumber(asn),
					 tonumber(network), mac,
					 tonumber(pool), tonumber(ipversion),
					 tonumber(protocol), traffic_type_filter,
					 filtered_hosts, blacklisted_hosts, top_hidden, anomalous, dhcp_hosts, cidr)

if(hosts_stats == nil) then total = 0 else total = hosts_stats["numHosts"] end
hosts_stats = hosts_stats["hosts"]

local now = os.time()
local vals = {}

local num = 0
if(hosts_stats ~= nil) then
   for key, value in pairs(hosts_stats) do
      num = num + 1
      postfix = string.format("0.%04u", num)

      vals[hosts_stats[key]["throughput_"..throughput_type]+postfix] = key
   end
end

if(sortOrder == "asc") then
   funct = asc
else
   funct = rev
end

local formatted_res = {}

for _key, _value in pairsByKeys(vals, funct) do
   local record = {}
   local key = vals[_key]
   local value = hosts_stats[key]

   local symkey = hostinfo2jqueryid(hosts_stats[key])
   record["key"] = symkey

   local drop_traffic = false
   if have_nedge and ntop.getHashCache("ntopng.prefs.drop_host_traffic", key) == "true" then
      drop_traffic = true
   end

   record["ip"] = stripVlan(key)

   local host = interface.getHostInfo(hosts_stats[key].ip, hosts_stats[key].vlan)

   if(value["name"] == nil) then
      local hinfo = hostkey2hostinfo(word)
      value["name"] = host2name(hinfo["host"], hinfo["vlan"])
   end

   if(value["name"] == "") then
      value["name"] = key
   end

   local column_name

   if(long_names) then
      column_name = value["name"]
   else
      column_name = shortHostName(value["name"])
   end

   record["name"] = column_name

   record["throughput"] = 0
   if((value["throughput_trend_"..throughput_type] ~= nil) and
      (value["throughput_trend_"..throughput_type] > 0)) then

      record["throughput"] = 8*value["throughput_bps"] --bits
   end

   formatted_res[#formatted_res + 1] = record
end -- for

print(json.encode(formatted_res))
