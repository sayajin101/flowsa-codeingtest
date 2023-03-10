# Before will always run even when starting from a prebuild
# This is why we add keys in the before script

# Add SSH key from env variable

mkdir -p /home/gitpod/.ssh

# Set up key forwarding and remove strict host key checks

echo "Host *
ForwardAgent yes
StrictHostKeyChecking no" > /home/gitpod/.ssh/config