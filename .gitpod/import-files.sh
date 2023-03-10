source .gitpod/add-ssh-forwarding.sh

rsync -avP deploy@HOSTNAME:/var/www/PROJECT_NAME/live/current/storage/public/. $GITPOD_REPO_ROOT/storage/public/.