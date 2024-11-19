// app_names -> app_basics
recreate(db.app_names, [
    {
        $sort: {
            last_ts: 1
        }
    },
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
            short_description : {
                $last: '$short_description'
            },
            description: {
                $last: '$description'
            },
            developer: {
                $last: '$developer'
            },
            star_rating: {
                $last: '$star_rating'
            },
            category: {
                $last: '$category'
            },
            categories: {
                $last: '$categories'
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
            short_description: '$short_description',
            description: '$description',
            developer: '$developer',
            star_rating: '$star_rating',
            category: '$category',
            categories: '$categories'
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
                $last: '$name'
            },
            icon: {
                $last: '$icon'
            },
            count: {
                $last: '$count'
            },
            short_description: {
                $last: '$short_description'
            },
            description: {
                $last: '$description'
            },
            developer: {
                $last: '$developer'
            },
            star_rating: {
                $last: '$star_rating'
            },
            category: {
                $last: '$category'
            },
            categories: {
                $last: '$categories'
            }
        }
    }

], { _id: 1 }, 'app_basics');
