/system script add name=kaafiye-setup source={

# ================================
# KAAFIYE WIFI HOTSPOT AUTO DEPLOY
# ================================

:log info "Starting Kaafiye Hotspot Setup..."

# ================================
# CLEAN OLD CONFIG
# ================================
:foreach i in=[/interface wireguard find] do={/interface wireguard remove $i}
:foreach i in=[/interface bridge find] do={/interface bridge remove $i}
:foreach i in=[/ip hotspot find] do={/ip hotspot remove $i}
:foreach i in=[/ip hotspot profile find] do={/ip hotspot profile remove $i}
:foreach i in=[/ip pool find] do={/ip pool remove $i}
:foreach i in=[/ip dhcp-server find] do={/ip dhcp-server remove $i}

# ================================
# WIREGUARD VPN
# ================================
/interface wireguard add name=kaafiyewifi-wg listen-port=0 mtu=1420 private-key="{{WG_PRIVATE_KEY}}"

/interface wireguard peers add \
 interface=kaafiyewifi-wg \
 public-key="{{WG_PUBLIC_KEY}}" \
 endpoint-address=vpn.kaafiyewifi.app \
 endpoint-port=443 \
 allowed-address=172.16.0.0/12 \
 persistent-keepalive=15s

/ip address add address=172.16.5.2/12 interface=kaafiyewifi-wg network=172.16.0.0

/ip route add dst-address=172.16.0.0/12 gateway=kaafiyewifi-wg comment="WireGuard VPN Route"

# ================================
# HOTSPOT TRAFFIC VIA VPN
# ================================
/ip firewall mangle add chain=prerouting src-address=10.5.50.0/24 action=mark-routing new-routing-mark=to-vpn
/ip route add dst-address=0.0.0.0/0 gateway=kaafiyewifi-wg routing-mark=to-vpn

# ================================
# FIREWALL ALLOW
# ================================
/ip firewall filter add chain=input protocol=udp dst-port=443 action=accept comment="Allow WireGuard"
/ip firewall filter add chain=forward src-address=10.5.50.0/24 action=accept comment="Hotspot to VPN"

# ================================
# HOTSPOT BRIDGE
# ================================
/interface bridge add name=kaafiyewifi-bridge
/interface bridge port add bridge=kaafiyewifi-bridge interface=ether2

/ip address add address=10.5.50.1/24 interface=kaafiyewifi-bridge

/ip pool add name=hotspot-pool ranges=10.5.50.2-10.5.50.254

/ip dhcp-server add name=dhcp-hotspot interface=kaafiyewifi-bridge address-pool=hotspot-pool disabled=no
/ip dhcp-server network add address=10.5.50.0/24 gateway=10.5.50.1 dns-server=8.8.8.8

# ================================
# RADIUS
# ================================
/radius add address=172.16.0.1 service=hotspot secret="{{RADIUS_SECRET}}" src-address=172.16.5.2 timeout=6s require-message-auth=no
/radius incoming set accept=yes port=3799

/ip firewall filter add chain=input protocol=udp dst-port=1812,1813,3799 action=accept comment="Allow Radius"

# ================================
# HOTSPOT PROFILE
# ================================
/ip hotspot profile add name="{{TITLE}}-profile" hotspot-address=10.5.50.1 use-radius=yes login-by=mac,http-chap mac-auth-mode=mac-as-username-and-password

/ip hotspot add name="{{TITLE}}" interface=kaafiyewifi-bridge profile="{{TITLE}}-profile" address-pool=hotspot-pool disabled=no

/ip hotspot user profile add name=default-rate rate-limit={{DOWNLOAD}}/{{UPLOAD}}

# Stability tuning
:foreach hs in=[/ip/hotspot/find] do={
 /ip/hotspot set $hs idle-timeout=00:00:30 keepalive-timeout=00:01:00 addresses-per-mac=1
}

# ================================
# NAT
# ================================
/ip firewall nat add chain=srcnat src-address=10.5.50.0/24 out-interface=kaafiyewifi-wg action=masquerade comment="Hotspot to VPN NAT"

# ================================
# DNS
# ================================
/ip dns set allow-remote-requests=yes servers=8.8.8.8,1.1.1.1

# ================================
# WALLED GARDEN
# ================================
/ip hotspot walled-garden add dst-host=*.kaafiyewifi.app

# ================================
# LOGIN PAGE AUTO FETCH
# ================================
:local htmlDir [/ip hotspot profile get [find name="{{TITLE}}-profile"] html-directory]
:foreach f in={"login.html";"status.html"} do={
 /tool fetch url="https://kaafiyewifi.app/router-files/$f" dst-path="$htmlDir/$f" duration=1m
}

# ================================
# FINISH
# ================================
:log info "Kaafiye Hotspot Setup Completed"

/system script remove [find name="kaafiye-setup"]
}

/system script run kaafiye-setup
