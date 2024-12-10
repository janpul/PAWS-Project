using System;
using System.Collections.Generic;
using System.Data.Entity.ModelConfiguration;
using System.Linq;
using System.Web;
using System.Web.Mvc;

namespace PAWSProject.Models
{
    public class tblpetMap : EntityTypeConfiguration<tblpetModel>
    {
        public tblpetMap()
        {
            HasKey(x => x.petID);
            ToTable("tblpet");
        }
    }
}