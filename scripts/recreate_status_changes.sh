#!/bin/bash

php recreate_status_changes.php

# mongosh mongodb://psm@localhost/psm -p fuwo1yeed0aekaizi2OoY4eiriequait --eval "db.status_changes_tmp.ensureIndex({ts: -1})"
# mongosh mongodb://psm@localhost/psm -p fuwo1yeed0aekaizi2OoY4eiriequait --eval "db.status_changes_tmp.renameCollection('status_changes', true)"
