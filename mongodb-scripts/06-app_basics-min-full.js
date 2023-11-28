// app_names -> app_basics
recreate(db.app_names, [
    {
        $group: {
            _id: {
                id: '$_id.id',
                name: '$name'
            },
            first_ts: {
                $min: '$first_ts'
            },
            last_ts: {
                $max: '$last_ts'
            },
            icon: {
                $last: '$icon'
            },
            count: {
                $sum: 1
            },
            developer: {
                $last: '$developer'
            }
        }
    }, {
        $project: {
            id: '$_id.id',
            name: '$_id.name',
            first_ts: '$first_ts',
            last_ts: '$last_ts',
            icon: '$icon',
            count: '$count',
            developer: '$developer',
        }
    }, {
        $sort: {
            id: 1,
            count: -1
        }
    }, {
        $group: {
            _id: '$id',
            first_ts: {
                $min: '$first_ts'
            },
            last_ts: {
                $max: '$last_ts'
            },
            name: {
                $first: '$name'
            },
            icon: {
                $first: '$icon'
            },
            developer: {
                $first: '$developer'
            }
        }
    }

], { _id: 1 }, 'app_basics');
