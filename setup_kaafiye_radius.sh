#!/usr/bin/env bash
set -euo pipefail

### ====== EDIT THESE ======
DB_NAME="kaafiye_wifi"
DB_USER="kaafiye"
DB_PASS="CHANGE_DB_PASSWORD"
RADIUS_SECRET="testing123"              # same secret you will use in MikroTik
MIKROTIK_LAN_IP="192.168.88.1"          # your MikroTik LAN IP (NAS)
PUBLIC_VPS_IP="154.23.45.67"            # your VPS public IP
### ========================

echo "==> Updating packages..."
apt update -y

echo "==> Installing FreeRADIUS + MariaDB client (server assumed installed) ..."
apt install -y freeradius freeradius-mysql mariadb-client ufw

echo "==> Creating DB and user (if not exists)..."
mysql -uroot <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "==> Importing FreeRADIUS schema..."
mysql -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql

echo "==> Ensuring nas table has localhost + Mikrotik..."
mysql -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "
INSERT IGNORE INTO nas (nasname, shortname, type, secret)
VALUES ('127.0.0.1','localhost','other','${RADIUS_SECRET}');
"

mysql -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "
INSERT INTO nas (nasname, shortname, type, secret)
VALUES ('${MIKROTIK_LAN_IP}','mikrotik1','mikrotik','${RADIUS_SECRET}')
ON DUPLICATE KEY UPDATE secret='${RADIUS_SECRET}', type='mikrotik', shortname='mikrotik1';
"

echo "==> Writing sql module config..."
cat >/etc/freeradius/3.0/mods-enabled/sql <<EOF
sql {
    driver = "rlm_sql_mysql"
    dialect = "mysql"

    server = "127.0.0.1"
    port = 3306
    login = "${DB_USER}"
    password = "${DB_PASS}"
    radius_db = "${DB_NAME}"

    read_groups = yes
    read_profiles = yes
    read_clients = yes

    sql_user_name = "%{User-Name}"
    safe_characters = "@abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.-_: /"

    \$INCLUDE \${modconfdir}/\${.:name}/main/\${dialect}/queries.conf
}
EOF

echo "==> Enabling sql in default + inner-tunnel (if not already)..."
# Ensure authorize section includes "sql" before files (common practice)
sed -i 's/^[[:space:]]*files$/\tsql\n\tfiles/' /etc/freeradius/3.0/sites-enabled/default || true
sed -i 's/^[[:space:]]*files$/\tsql\n\tfiles/' /etc/freeradius/3.0/sites-enabled/inner-tunnel || true

echo "==> Setting permissions..."
chown -R freerad:freerad /etc/freeradius/3.0 || true

echo "==> UFW allow RADIUS ports..."
ufw allow 1812/udp
ufw allow 1813/udp
ufw reload || true

echo "==> Restarting FreeRADIUS..."
systemctl restart freeradius
systemctl enable freeradius

echo "==> Quick self-test user insert..."
mysql -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "
INSERT INTO radcheck (username, attribute, op, value)
VALUES ('ahmed1','Cleartext-Password',':=','123456')
ON DUPLICATE KEY UPDATE value='123456';
"

mysql -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "
INSERT INTO radreply (username, attribute, op, value)
VALUES ('ahmed1','Mikrotik-Rate-Limit',':=','2M/2M')
ON DUPLICATE KEY UPDATE value='2M/2M';
"

echo ""
echo "✅ DONE!"
echo "VPS IP: ${PUBLIC_VPS_IP}"
echo "RADIUS Secret: ${RADIUS_SECRET}"
echo "MikroTik NAS: ${MIKROTIK_LAN_IP}"
echo ""
echo "Next: Run MikroTik script (below) to point hotspot to RADIUS."
