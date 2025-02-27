ENDPOINT_URL="http://localhost:4566"
REGION="us-east-1"

export AWS_ACCESS_KEY_ID=test
export AWS_SECRET_ACCESS_KEY=test

create_bucket() {
    BUCKET_NAME=$1
    aws --endpoint-url=$ENDPOINT_URL s3 mb s3://$BUCKET_NAME --region $REGION
}

create_bucket "artist-avatar"
create_bucket "album-cover"
create_bucket "track"