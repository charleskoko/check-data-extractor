class CheckDataExtractor < Formula
  desc "Allows retrieving transaction information from the database."
  homepage "https://github.com/charleskoko/check-data-extractor"
  url "https://github.com/charleskoko/check-data-extractor/archive/refs/tags/v1.2.4.tar.gz"
  sha256 "080f077a4a1237e438301059d9f2e70a8d22092fd6cd8d70fa9de7abfd1feba7"
  version "1.2.4"

  depends_on "php"

  def install
      bin.install "check.php" => "check"
    end
end
