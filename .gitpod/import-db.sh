source .gitpod/add-ssh-forwarding.sh
ddev snapshot
ssh -C deploy@HOSTNAME "mysqldump --lock-tables=false example_live" > /tmp/db.sql
cat /tmp/db.sql | ddev import-db
