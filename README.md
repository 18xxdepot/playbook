# playbooks
Ansible Playbooks for 18xxdepot

## Usage

First install ansible. For macs this can be done with
[homebrew](https://brew.sh/).

Run with:

``` sh
ansible-playbook -K site.yml
```

When it asks for the `BECOME` password it wants your password in order to run
`sudo` on the remote server.

If your SSH config isn't setup for the right user on the 18xxdepot box you will
also need to pass in a username:

``` sh
ansible-playbook -u kelsin -K site.yml
```
