// main -> numeric_requests
recreate(db.main, [
    {
        $project: {
            id: '$request.id',
            territory: '$request.gl',
            ts: '$ts',
            available: '$respone.available'
        }
    }
], { id: 1 }, 'numeric_requests');
