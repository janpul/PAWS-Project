using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace PAWSProject.Models
{
    public class tblpetModel
    {
        public int petID { get; set; }
        public string name { get; set; }
        public string gender { get; set; }
        public string age { get; set; }
        public string size { get; set; }
        public string details { get; set; }
        public string imagePath { get; set; }
        public DateTime createdAt { get; set; }
        public DateTime updateAt { get; set; }
    }
}
