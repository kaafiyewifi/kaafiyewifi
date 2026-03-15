# ================================
# KAAFIYE PROVISION SCRIPT (FINAL)
# RouterOS v7 compatible
# ================================

:global kfCallbackUrl "https://kaafiye.online/api/provision/callback/278F-xDmpFk2nIfEy_pF4NkCcXEUYOJYPDH0pFvN6B0";
:global kfHeartbeatUrl "https://kaafiye.online/api/routers/heartbeat";

:global kfApiPort 8728;
:global kfApiUser "kaafiye";
:global kfApiPass "SuperStrongPasswordHere";
:global kfTz "Africa/Mogadishu";

# WireGuard server settings
:global kfWgEndpoint "vpn.kaafiye.online";
:global kfWgPort 51820;
:global kfWgServerPub "tcX7WwQ6B0bVzMCYrFw9ipbV0TWu8JmV1sNGshh8JlI=";

# RADIUS server settings (per-router secret)
:global kfRadiusIp "34.30.227.130";
:global kfRadiusSecret "5f16b6a6adcdaece3784d0547d3f5643";

# Hotspot files base url
:global kfHotspotBase "https://kaafiye.online/hotspot";

:put "-----------------Downloading configuration-----------------";
:put "Downloading configuration...";
:put "-----------------Applying configuration-----------------";
:put "Applying configuration...";

:log info "Starting Kaafiye provisioning...";

# --- Timezone ---
:do {
  /system clock set time-zone-name=$kfTz;
  :log info "Timezone configured successfully";
  :put "-----------------Timezone configured successfully-----------------";
} on-error={
  :log warning "Timezone set failed";
  :put "-----------------Timezone set FAILED-----------------";
}

# --- Detect mgmt IP (best effort) ---
:local kfMgmtIp "";

:do {
  :local idx [/ip address find where dynamic=yes disabled=no];
  :if ([:len $idx] > 0) do={
    :local a [/ip address get [:pick $idx 0] address];
    :set kfMgmtIp [:pick $a 0 [:find $a "/"]];
  }
} on-error={}

:if ($kfMgmtIp = "") do={
  :foreach ifn in={"bridgeLocal";"bridge";"ether1";"ether2"} do={
    :do {
      :local idx2 [/ip address find where interface=$ifn disabled=no];
      :if ([:len $idx2] > 0) do={
        :local a2 [/ip address get [:pick $idx2 0] address];
        :set kfMgmtIp [:pick $a2 0 [:find $a2 "/"]];
      }
    } on-error={}
    :if ($kfMgmtIp != "") do={ :break; }
  }
}

:log info ("Kaafiye mgmt_ip=" . $kfMgmtIp);
:put ("-----------------Detected mgmt_ip=" . $kfMgmtIp . "-----------------");

# =====================================================================
# WIREGUARD (router auto-generates keys)
# =====================================================================
:put "-----------------Downloading WireGuard configuration file-----------------";

:local kfWgName "kaafiye-wg";
:local kfRouterWgPub "";

:do {
  :if ([:len [/interface wireguard find where name=$kfWgName]] = 0) do={
    /interface wireguard add name=$kfWgName listen-port=0 mtu=1420 comment="KAAFIYE";
  }

  :set kfRouterWgPub [/interface wireguard get [find where name=$kfWgName] public-key];

  :if ([:len [/interface wireguard peers find where interface=$kfWgName && public-key=$kfWgServerPub]] = 0) do={
    /interface wireguard peers add interface=$kfWgName \
      public-key=$kfWgServerPub \
      endpoint-address=$kfWgEndpoint \
      endpoint-port=$kfWgPort \
      allowed-address=0.0.0.0/0 \
      persistent-keepalive=25s \
      comment="KAAFIYE-SERVER";
  }

  :put "-----------------Applying WireGuard configuration-----------------";
  :log info ("WireGuard configured; router_pub=" . $kfRouterWgPub);
} on-error={
  :log warning "WireGuard configuration failed";
  :put "-----------------WireGuard configuration FAILED-----------------";
}

# =====================================================================
# PHASE 2 - NAT / INTERNET SHARING (Masquerade)
# =====================================================================
:put "-----------------Removed existing masquerade rules-----------------";

:do {
  :foreach n in=[/ip firewall nat find chain=srcnat action=masquerade] do={
    /ip firewall nat remove $n;
  }
} on-error={
  :log warning "Failed removing old masquerade rules";
}

:put "-----------------Added masquerade rule for entire network-----------------";

:do {
  /ip firewall nat add chain=srcnat action=masquerade out-interface=ether1 comment="KAAFIYE-WIFI";
} on-error={
  :log warning "Failed adding masquerade rule";
}

# =====================================================================
# PHASE 3 - Hotspot Profile + Walled Garden
# =====================================================================
:put "---------------Configuring Hotspot Profile-----------------";

:do {
  /ip hotspot profile set [find name="hsprof1"] dns-name="login.kaafiye.online" html-directory="hotspot" use-radius=yes;
  :put "-----------------Hotspot profile configured successfully-----------------";
} on-error={
  :log warning "Hotspot profile configuration failed";
  :put "-----------------Hotspot profile configuration FAILED-----------------";
}

:put "---------------Cleaning old Kaafiye walled-garden-----------------";

:do {
  :foreach w in=[/ip hotspot walled-garden find comment="Kaafiye WG host"] do={ /ip hotspot walled-garden remove $w; }
  :foreach w in=[/ip hotspot walled-garden find comment="KAAFIYE-WG"] do={ /ip hotspot walled-garden remove $w; }

  :foreach w in=[/ip hotspot walled-garden find dst-host="k"] do={ /ip hotspot walled-garden remove $w; }
  :foreach w in=[/ip hotspot walled-garden find dst-host="a"] do={ /ip hotspot walled-garden remove $w; }
  :foreach w in=[/ip hotspot walled-garden find dst-host="f"] do={ /ip hotspot walled-garden remove $w; }
  :foreach w in=[/ip hotspot walled-garden find dst-host="i"] do={ /ip hotspot walled-garden remove $w; }
  :foreach w in=[/ip hotspot walled-garden find dst-host="y"] do={ /ip hotspot walled-garden remove $w; }
  :foreach w in=[/ip hotspot walled-garden find dst-host="e"] do={ /ip hotspot walled-garden remove $w; }

  :put "-----------------Old Kaafiye walled-garden removed-----------------";
} on-error={
  :log warning "Failed removing old Kaafiye walled-garden";
  :put "-----------------Old Kaafiye walled-garden remove FAILED-----------------";
}

:put "---------------Adding Kaafiye walled-garden-----------------";

:do {
  /ip hotspot walled-garden add dst-host="login.kaafiye.online" action=allow comment="KAAFIYE-WG";
  /ip hotspot walled-garden add dst-host="app.kaafiye.online" action=allow comment="KAAFIYE-WG";
  /ip hotspot walled-garden add dst-host="kaafiye.online" action=allow comment="KAAFIYE-WG";
  :put "-----------------Walled garden rules added successfully-----------------";
} on-error={
  :log warning "Failed adding walled-garden rules";
  :put "-----------------Walled garden rules add FAILED-----------------";
}

:put "---------------Phase 3 completed successfully-----------------";

# =====================================================================
# PHASE 4A - Download hotspot files (SERVER HOSTED)
# FIX: RouterOS v7 uses http-timeout (NOT timeout)
# Also: remove file before fetch to force overwrite
# =====================================================================
:put "---------------Downloading hotspot files-----------------";

:do { /file make-directory hotspot/css; } on-error={}
:do { /file make-directory hotspot/img; } on-error={}
:do { /file make-directory hotspot/xml; } on-error={}

:local base $kfHotspotBase;

:do { /file remove hotspot/login.html; } on-error={}
:do { :local u ($base . "/login.html"); /tool fetch mode=https check-certificate=no http-timeout=30s output=file dst-path=hotspot/login.html url=$u; } on-error={ :log warning "Fetch failed login.html"; }

:do { /file remove hotspot/alogin.html; } on-error={}
:do { :local u ($base . "/alogin.html"); /tool fetch mode=https check-certificate=no http-timeout=30s output=file dst-path=hotspot/alogin.html url=$u; } on-error={ :log warning "Fetch failed alogin.html"; }

:do { /file remove hotspot/error.html; } on-error={}
:do { :local u ($base . "/error.html"); /tool fetch mode=https check-certificate=no http-timeout=30s output=file dst-path=hotspot/error.html url=$u; } on-error={ :log warning "Fetch failed error.html"; }

:do { /file remove hotspot/logout.html; } on-error={}
:do { :local u ($base . "/logout.html"); /tool fetch mode=https check-certificate=no http-timeout=30s output=file dst-path=hotspot/logout.html url=$u; } on-error={ :log warning "Fetch failed logout.html"; }

:do { /file remove hotspot/status.html; } on-error={}
:do { :local u ($base . "/status.html"); /tool fetch mode=https check-certificate=no http-timeout=30s output=file dst-path=hotspot/status.html url=$u; } on-error={ :log warning "Fetch failed status.html"; }

:do { /file remove hotspot/redirect.html; } on-error={}
:do { :local u ($base . "/redirect.html"); /tool fetch mode=https check-certificate=no http-timeout=30s output=file dst-path=hotspot/redirect.html url=$u; } on-error={ :log warning "Fetch failed redirect.html"; }

:do { /file remove hotspot/md5.js; } on-error={}
:do { :local u ($base . "/md5.js"); /tool fetch mode=https check-certificate=no http-timeout=30s output=file dst-path=hotspot/md5.js url=$u; } on-error={ :log warning "Fetch failed md5.js"; }

:do { /file remove hotspot/favicon.ico; } on-error={}
:do { :local u ($base . "/favicon.ico"); /tool fetch mode=https check-certificate=no http-timeout=30s output=file dst-path=hotspot/favicon.ico url=$u; } on-error={ :log warning "Fetch failed favicon.ico"; }

:do { /file remove hotspot/css/style.css; } on-error={}
:do { :local u ($base . "/css/style.css"); /tool fetch mode=https check-certificate=no http-timeout=30s output=file dst-path=hotspot/css/style.css url=$u; } on-error={ :log warning "Fetch failed css/style.css"; }

:do { /file remove hotspot/img/user.svg; } on-error={}
:do { :local u ($base . "/img/user.svg"); /tool fetch mode=https check-certificate=no http-timeout=30s output=file dst-path=hotspot/img/user.svg url=$u; } on-error={ :log warning "Fetch failed img/user.svg"; }

:do { /file remove hotspot/img/password.svg; } on-error={}
:do { :local u ($base . "/img/password.svg"); /tool fetch mode=https check-certificate=no http-timeout=30s output=file dst-path=hotspot/img/password.svg url=$u; } on-error={ :log warning "Fetch failed img/password.svg"; }

:do { /file remove hotspot/xml/WISPAccessGatewayParam.xsd; } on-error={}
:do { :local u ($base . "/xml/WISPAccessGatewayParam.xsd"); /tool fetch mode=https check-certificate=no http-timeout=30s output=file dst-path=hotspot/xml/WISPAccessGatewayParam.xsd url=$u; } on-error={ :log warning "Fetch failed xml/WISPAccessGatewayParam.xsd"; }

:do { /file remove hotspot/xml/alogin.html; } on-error={}
:do { :local u ($base . "/xml/alogin.html"); /tool fetch mode=https check-certificate=no http-timeout=30s output=file dst-path=hotspot/xml/alogin.html url=$u; } on-error={ :log warning "Fetch failed xml/alogin.html"; }

:do { /file remove hotspot/xml/error.html; } on-error={}
:do { :local u ($base . "/xml/error.html"); /tool fetch mode=https check-certificate=no http-timeout=30s output=file dst-path=hotspot/xml/error.html url=$u; } on-error={ :log warning "Fetch failed xml/error.html"; }

:put "-----------------Downloaded hotspot files successfully-----------------";
:put "---------------Phase 4A completed successfully-----------------";

# =====================================================================
# RADIUS (hotspot,login)
# =====================================================================
:put "-----------------Configuring RADIUS-----------------";

:do {
  :if ($kfRadiusSecret = "") do={
    :log warning "RADIUS secret missing (per-router)";
    :put "-----------------RADIUS secret missing-----------------";
  } else={
    :local kfRid [/radius find where address=$kfRadiusIp];

    :if ([:len $kfRid] > 0) do={
      /radius set [:pick $kfRid 0] address=$kfRadiusIp secret=$kfRadiusSecret authentication-port=1812 accounting-port=1813 timeout=300ms src-address=$kfMgmtIp service=hotspot,login comment="KAAFIYE";
      :log info "RADIUS updated successfully";
      :put "-----------------RADIUS updated successfully-----------------";
    } else={
      /radius add service=hotspot,login address=$kfRadiusIp secret=$kfRadiusSecret authentication-port=1812 accounting-port=1813 timeout=300ms src-address=$kfMgmtIp comment="KAAFIYE";
      :log info "RADIUS server added successfully";
      :put "-----------------RADIUS server added successfully-----------------";
    }

    :do { /ip hotspot profile set [find where name="default"] use-radius=yes radius-accounting=yes; } on-error={}
  }
} on-error={
  :log warning "RADIUS configuration failed";
  :put "-----------------RADIUS configuration FAILED-----------------";
}

# =====================================================================
# CALLBACK (GET)
# =====================================================================
:put "-----------------Notifying server (callback)-----------------";

:local kfIdent [/system identity get name];
:local kfCbUrl ($kfCallbackUrl . "?identity=" . $kfIdent . "&mgmt_ip=" . $kfMgmtIp . "&api_port=" . $kfApiPort);

:if ($kfRouterWgPub != "") do={
  :set kfCbUrl ($kfCbUrl . "&wg_pub=" . $kfRouterWgPub);
}

:do {
  /tool fetch mode=https check-certificate=no output=file dst-path=cb.txt url=$kfCbUrl;
  :put "-----------------Callback sent successfully-----------------";
  :log info "Callback sent successfully";
} on-error={
  :log warning ("Callback failed url=" . $kfCbUrl);
  :put "-----------------Callback FAILED-----------------";
}

:do { /file remove cb.txt; } on-error={}

# =====================================================================
# HEARTBEAT Scheduler (1m)
# =====================================================================
:put "-----------------Configuring heartbeat-----------------";

:do { /system scheduler remove [find name="kaafiye-heartbeat"]; } on-error={}

:do {
  /system scheduler add name="kaafiye-heartbeat" start-time=startup interval=1m on-event="\
:local ident [/system identity get name];\
:local cpu [/system resource get cpu-load];\
:local freeMem [/system resource get free-memory];\
:local totalMem [/system resource get total-memory];\
:local freeHdd [/system resource get free-hdd-space];\
:local totalHdd [/system resource get total-hdd-space];\
:local uptime [/system resource get uptime];\
:local verFull [/system resource get version];\
:local sp [:find \$verFull \" \"];\
:local ver \$verFull;\
:if (\$sp != nil) do={ :set ver [:pick \$verFull 0 \$sp]; };\
:local board [/system resource get board-name];\
:local arch [/system resource get architecture-name];\
:local hbUrl \$kfHeartbeatUrl;\
:set hbUrl (\$hbUrl . \"?identity=\" . \$ident);\
:set hbUrl (\$hbUrl . \"&cpu_load=\" . \$cpu);\
:set hbUrl (\$hbUrl . \"&free_memory=\" . \$freeMem);\
:set hbUrl (\$hbUrl . \"&total_memory=\" . \$totalMem);\
:set hbUrl (\$hbUrl . \"&free_hdd_space=\" . \$freeHdd);\
:set hbUrl (\$hbUrl . \"&total_hdd_space=\" . \$totalHdd);\
:set hbUrl (\$hbUrl . \"&uptime=\" . \$uptime);\
:set hbUrl (\$hbUrl . \"&version=\" . \$ver);\
:set hbUrl (\$hbUrl . \"&board_name=\" . \$board);\
:set hbUrl (\$hbUrl . \"&architecture_name=\" . \$arch);\
:do { /tool fetch mode=https check-certificate=no output=file dst-path=hb.txt url=\$hbUrl; } on-error={ :log warning (\"Kaafiye heartbeat failed url=\" . \$hbUrl); };\
:do { /file remove hb.txt; } on-error={};";
  :log info "Heartbeat scheduler added";
  :put "-----------------Heartbeat scheduler added-----------------";
} on-error={
  :log warning "Heartbeat scheduler add failed";
  :put "-----------------Heartbeat scheduler add FAILED-----------------";
}

:put "-----------------Provisioning completed-----------------";
:put "Provisioning completed";
:log info "Configuration completed successfully.";
