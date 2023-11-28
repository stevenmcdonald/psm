
Anible files to set up server(s) for PSM
========================================

Initial setup:
--------------

python -m venv venv
. ./venv/bin/activate
pip install -r requirements.txt
ansible-galaxy collection install community.mongodb

Update Apt and enable unattended-upgrades:
---------------------------------------------

ansible-playbook -i production.ini server.yaml

Install MongoDB:
----------------

ansible-playbook -i production.ini install_mongodb.yaml

