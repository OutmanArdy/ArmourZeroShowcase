terraform {
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
  required_version = ">= 1.0.0"
}

provider "aws" {
  region = "us-east-1"
}

terraform {
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }

  required_version = ">= 1.0.0"
}

provider "aws" {
  region = "us-east-1"
}

# ✅ SECURE SECURITY GROUP
resource "aws_security_group" "secure_sg" {
  name        = "restricted-access"
  description = "Security group with restricted ingress and controlled egress"

  # Restrict SSH access only from your office/VPN (example: private CIDR)
  ingress {
    description = "Allow SSH from trusted network"
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["10.0.0.0/16"] # replace with your real private/VPN CIDR
  }

  # Allow HTTP only from trusted corporate/VPN network (NOT 0.0.0.0/0)
  ingress {
    description = "Allow HTTP from trusted network"
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["10.0.0.0/16"]
  }

  # Explicit egress: only HTTPS outbound, limited to private CIDR
  egress {
    description = "Allow outbound HTTPS only to trusted range"
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["10.0.0.0/16"]
  }
}


# ✅ KMS KEY FOR ENCRYPTION
resource "aws_kms_key" "mykey" {
  description             = "KMS key for S3 encryption"
  deletion_window_in_days = 30
  enable_key_rotation     = true
}

# ✅ SECURE S3 BUCKET
resource "aws_s3_bucket" "secure_bucket" {
  bucket = "my-secure-bucket-demo"
  acl    = "private"

  versioning {
    enabled = true
  }

  logging {
    target_bucket = aws_s3_bucket.log_bucket.id
    target_prefix = "log/"
  }
}

# ✅ LOGGING BUCKET (with protections)
resource "aws_s3_bucket" "log_bucket" {
  bucket = "my-secure-bucket-demo-logs"
  acl    = "log-delivery-write"

  versioning {
    enabled = true
  }
}

# Public access block for both buckets
resource "aws_s3_bucket_public_access_block" "secure_block" {
  bucket                  = aws_s3_bucket.secure_bucket.id
  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

resource "aws_s3_bucket_public_access_block" "log_block" {
  bucket                  = aws_s3_bucket.log_bucket.id
  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

# ✅ ENCRYPTION ENABLED (both buckets)
resource "aws_s3_bucket_server_side_encryption_configuration" "secure_enc" {
  bucket = aws_s3_bucket.secure_bucket.id

  rule {
    apply_server_side_encryption_by_default {
      sse_algorithm     = "aws:kms"
      kms_master_key_id = aws_kms_key.mykey.arn
    }
  }
}

resource "aws_s3_bucket_server_side_encryption_configuration" "log_enc" {
  bucket = aws_s3_bucket.log_bucket.id

  rule {
    apply_server_side_encryption_by_default {
      sse_algorithm     = "aws:kms"
      kms_master_key_id = aws_kms_key.mykey.arn
    }
  }
}
