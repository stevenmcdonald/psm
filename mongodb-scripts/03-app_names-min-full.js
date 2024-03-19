// main -> app_names
recreate(db.main, [
    {
        $project: {
            territory: '$request.gl',
            id: '$request.id',
            ts: '$ts',
            icon: '$response.icon',
            name: '$response.name',
            short_description: '$response.short_description',
            description: '$response.description',
            developer: '$response.developer',
            star_rating: '$response.star_rating',
            category: '$response.category',
            categories: '$response.categories'
        }
    }, {
        $group: {
            _id: {
                id: '$id',
                territory: '$territory'
            },
            name: {
                $last: '$name'
            },
            icon: {
                $last: '$icon'
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
            },
            first_ts: {
                $min: '$ts'
            },
            last_ts: {
                $max: '$ts'
            }
        }
    }
], {'_id.id': 1, '_id.territory': 1}, 'app_names');
