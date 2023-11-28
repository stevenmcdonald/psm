// main -> app_categories (called app_genres in ASM)
db.main.ensureIndex({'request.id': 1, 'request.gl': 1});
recreate(db.main, [
    {
        $group: {
            _id: {
                id: '$request.id',
                territory: '$request.gl'
            },
            category: {
                $last: '$response.category'
            }
        }
    }
], {'_id.id': 1, '_id.territory': 1}, 'app_categories');
